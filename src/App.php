<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use PDO, LogicException, Closure;

use zay\Module;
use zay\exceptions\VerifyFailException;
use zay\exceptions\ResponseEndException;
use zay\exts\SessionHandlerFiles;
use zay\exts\SessionHandlerRedis;
use zay\traits\Singleton;

final class App {

  private static ?self $instance = null;

  public static function getInstance() : static {
    return static::$instance ?? static::$instance = new static();
  }

  private function __construct() {}

  public Module $root;

  public array $controllers = [];

  public array $handles = [];

  public array $urls = [];

  public array $routes = [];

  public array $regexps = [];

  public array $verifys = [];

  public array $pipes = [];

  public array $views = [];

  public array $commands = [];

  public array $modules = [];

  public array $moduleNames = [];

  public array $moduleUrls = [];

  public function init(string ...$moduleNames) : static {
    $this->loadModules(...$moduleNames);
    $this->sortRoutes();
    $this->sortModuleUrls();
    $this->initSql();
    return $this;
  }

  private function loadModules(string ...$moduleNames) : static {
    $moduleNames = !empty($moduleNames) ? $moduleNames : ['index'];
    $root = $this->root = new Module();
    $root->app = $this;
    $root->root = $root;
    $root->parent = null;
    $root->path = APP_SRC;
    $root->namespace = 'app';
    $root->name = '';
    $root->url = '';
    $root->load(...$moduleNames);
    return $this;
  }

  private function sortRoutes() : void {
    usort($this->routes, function($a, $b) {
      if ($a['url'] != $b['url']) {
        return $a['url'] < $b['url'] ? 1 : -1;
      } elseif ($a['prefix'] != $b['prefix']) {
        return $a['prefix'] < $b['prefix'] ? 1 : -1;
      } elseif ($a['regexp'] == $b['regexp']) {
        return $a['regexp'] < $b['regexp'] ? 1 : -1;
      } else {
        return 0;
      }
    });
  }

  private function sortModuleUrls() : void {
    krsort($this->moduleUrls, SORT_STRING);
  }

  private function initSql() : void {
    $host     = env('APP_DB_HOST',     '127.0.0.1'); // 默认主机
    $port     = env('APP_DB_PORT',     '3306');      // 默认端口
    $username = env('APP_DB_USERNAME', 'root');      // 默认账户
    $password = env('APP_DB_PASSWORD', 'root');      // 默认密码
    $dbname   = env('APP_DB_DBNAME',   'zay');       // 默认数据库名称
    $charset  = env('APP_DB_CHARSET',  'utf8mb4');   // 默认字符集
    $dsn      = env('APP_DB_DSN', sprintf("mysql:host=%s:%d;dbname=%s;charset=%s;", $host, $port, $dbname, $charset));
    $options = array(
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    );
    Model::$conn = Sql::$conn = new PDO($dsn, $username, $password, $options);
  }

  public function route(Request $request) : string {
    if ($request->url == '/') { return $this->modules[1]->fullname.'/index/index'; }
    $url = rtrim($request->url, '/');
    $module = $this->modules[1];
    if (APP_ENGINE == 'swoole') {
      $urls = [
        $url,                                     // /admin/user/detail
        $module->fullurl . $url,                  // /user/detail -> /index/user/detail
        $module->fullurl . $url . '/index',       // /user        -> /index/user/index
        $module->fullurl . '/index' . $url,       // /user        -> /index/index/user
        $module->fullurl . $url . '/index/index', // ""           -> /index/index/index
        $url . '/index',                          // /admin/user  -> /admin/user/index
        $url . '/index/index',                    // /admin       -> /admin/index/index
      ];
      foreach ($urls as $u) {
        if (array_key_exists($u, $this->urls)) {
          return $this->urls[$u];
        }
      }
    } else {
      foreach ($this->moduleUrls as $moduleUrl => $m) {
        if ($url == $moduleUrl || $url == $moduleUrl.'/') { return $m->fullname.'/index/index'; }
        if (!empty($moduleUrl) && str_starts_with($url, $moduleUrl)) {
          $url = substr($url, strlen($moduleUrl));
          $module = $m;
          break;
        }
      }
      [$c, $t] = explode('/', trim($url, '/').'/index/index');
      $c = kebab2camel($c);
      $t = kebab2camel($t);
      $namespaceClassname = $module->namespace.'\\controllers\\'.camel2pascal($c).'Controller';
      if (class_exists($namespaceClassname) && method_exists($namespaceClassname, $t)) {
        return $module->fullname.'/'.$c.'/'.$t;
      }
    }
    foreach ($this->routes as $route) {
      if (!empty($route['url']) && $route['url'] === $url) {
        $request->get = array_merge($request->get, $route['get']);
        $request->post = array_merge($request->post, $route['post']);
        return $route['handler'];
      } elseif (!empty($route['regexp']) && str_starts_with($url, $route['prefix']) && preg_match($route['regexp'], substr($url, strlen($route['prefix'])), $_PARAMS)) {
        foreach ($_PARAMS as $key => $val) {
          if (ctype_digit("$key")) { continue; }
          $request->get[$key] = $val;
        }
        $request->get = array_merge($request->get, $route['get']);
        $request->post = array_merge($request->post, $route['post']);
        return $route['handler'];
      }
    }
    return '';
  }

  public function handle(Request $request, Response $response) : void {
    $handler = $this->route($request);
    if (!$handler) {
      $response->setStatus(404, 'Not Found')->sendResponse();
      return;
    }
    $handle = null;
    if (APP_ENGINE == 'swoole') {
      if (!array_key_exists($handler, $this->handles)) {
        $response->setStatus(404, 'Not Found')->sendResponse();
        return;
      }
      $handle = $this->handles[$handler];
    } else {
      [$m, $c, $t] = explode('/', $handler);
      if (!array_key_exists($m, $this->moduleNames)) {
        $response->setStatus(404, 'Not Found')->sendResponse();
        return;
      }

      $module = $this->moduleNames[$m];

      $namespaceClassname = $module->namespace.'\\controllers\\'.camel2pascal($c).'Controller';
      if (!class_exists($namespaceClassname) || !method_exists($namespaceClassname, $t)) {
        $response->setStatus(404, 'Not Found')->sendResponse();
        return;
      }
      $controller = new $namespaceClassname();
      $controller->app = $module->app;
      $controller->module = $module;
      $controller->name = $c; // pascal2camel(substr($classname, 0, -10));
      $controller->fullname = $module->fullname.'/'.$controller->name;
      $controller->url = camel2kebab($controller->name);
      $controller->fullurl = $module->fullurl.'/'.$controller->url;

      $handle = new Handler();
      $handle->app = $module->app;
      $handle->module = $module;
      $handle->controller = $controller;
      $handle->name = $t; // pascal2camel($method->name);
      $handle->fullname = $module->fullname.'/'.$controller->name.'/'.$handle->name;
      $handle->url = camel2kebab($handle->name);
      $handle->fullurl = $controller->fullurl.'/'.$handle->url;
      $handle->method = Closure::fromCallable([$controller, $t]);

      // TODO session_set_save_handler(new SessionHandlerFiles(), true);
      // TODO session_set_save_handler(new SessionHandlerRedis(), true);
      session_start();
      $request->_SESSION = $_SESSION;

    }
    try {
      $handle->handle($request, $response);
    } catch (ResponseEndException $e) {
    } catch (VerifyFailException $e) {
      $this->exception($response, $e, ['errcolumn' => $e->getErrorColumn()]);
    } catch (Exception $e) {
      $this->exception($response, $e, []);
    } catch (\Exception $e) {
      $this->exception($response, $e);
    }
    $response->sendResponse();
  }

  private function exception(Response $response, \Exception $e, array $data = []) : void {
    $data['errno'] = $e->getCode();
    $data['errmsg'] = $e->getMessage();
    if (APP_DEBUG) {
      $data['errfile'] = $e->getErrorFile();
      $data['errline'] = $e->getErrorLine();
    }
    $response->write(json_encode_object($data));
  }

  public function start() : void {
    if (empty($this->modules)) { throw new LogicException('No modules found! please call enableModules method.'); }
    if (APP_ENGINE != 'swoole') {
      $this->handle(Request::fromFpmRequest(), Response::fromFpmResponse());
      return;
    }
    \Swoole\Runtime::enableCoroutine();
    \Swoole\Coroutine\run(function() {
      $mimes = require __DIR__.'/files/mime.php';
      $server = new \Swoole\Coroutine\Http\Server(APP_SWOOLE_HOST, APP_SWOOLE_PORT, false);
      // $server->handle('/favicon.ico', function($req, $res) { $res->status(404); });
      // $server->handle('/reboot.txt', function($req, $res) { $res->status(404); });
      $server->handle('/', function($req, $res) use ($mimes) {
        $file = APP_PUBLIC.$req->server['request_uri'];
        if (is_file($file)) {
          $ext = pathinfo($req->server['request_uri'], PATHINFO_EXTENSION);
          $res->header('Content-Type', array_key_exists($ext, $mimes) ? $mimes[$ext] : 'application/octet-stream');
          $res->sendfile($file);
          return;
        }
        $this->handle(Request::fromSwooleHttpRequest($req), Response::fromSwooleHttpResponse($res));
      });
      $server->start();
    });
  }
}
