<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use zay\exceptions\VerifyException;
use zay\exceptions\ResponseEndException;
use zay\sessions\SessionHandlerFiles;
use zay\traits\Singleton;

final class App {

  use Singleton;

  private array $modules = [];
  private array $moduleNames = [];
  private array $moduleAlias = [];

  public function enableModules(string ...$moduleNames) : static {
    $this->moduleNames = $moduleNames;
    foreach ($this->moduleNames as $moduleName) {
      $moduleClass = "\\app\\modules\\$moduleName\\Module";
      $module = $moduleClass::getInstance();
      $this->modules[$moduleName] = $module;
      $this->moduleAlias[$module->getUrl()] = $module->getName();
    }
    return $this;
  }

  private array $routes = [];

  public function addRoute(array $route) : static {
    array_push($this->routes, array_merge([
      'url' => '', // '/news.html' url路径
      'prefix' => '', // '/news-' 正则前缀
      'regexp' => '', // '#^/news-(?P<id>\d+)\.html$#' 正则表达式
      'action' => '', // 'index.index.index' 控制器
      'get' => [], // 注入的get参数
      'post' => [], // 注入的post参数
    ], $route));
    return $this;
  }

  public function route(Request $request) : string {
    $url = parse_url($request->server['REQUEST_URI'], PHP_URL_PATH);
    if ($url === '/') { return $this->moduleNames[0] . '.index.index'; }
    foreach ($this->routes as $route) {
      if (!empty($route['url']) && $route['url'] === $url) {
        $request->get = array_merge($request->get, $route['get']);
        $request->post = array_merge($request->post, $route['post']);
        return $route['action'];
      } else if (!empty($route['regexp']) && str_starts_with($url, $route['prefix']) && preg_match($route['regexp'], $url, $_PARAMS)) {
        foreach ($_PARAMS as $key => $val) {
          if (ctype_digit("$key")) { continue; }
          $request->get[$key] = $val;
        }
        $request->get = array_merge($request->get, $route['get']);
        $request->post = array_merge($request->post, $route['post']);
        return $route['action'];
      }
    }
    [$moduleName, $controllerName, $methodName] = explode('/', trim($url, '/') . '/index/index/index');
    if (array_key_exists($moduleName, $this->moduleAlias)) { $moduleName = $this->moduleAlias[$moduleName]; }
    if (!array_key_exists($moduleName, $this->modules)) { [$moduleName, $controllerName, $methodName] =  [$this->moduleNames[0], $moduleName, $controllerName]; }
    $controllerName = kebab2camel($controllerName);
    $methodName = kebab2camel($methodName);
    return "$moduleName.$controllerName.$methodName";
  }

  public function handle(Request $request, Response $response) : static {
    try {
      $actionName = $this->route($request);
      [$moduleName, $controllerName, $methodName] = explode('.', $actionName);
      $request->actionName = $response->actionName = $actionName;
      $request->moduleName = $response->moduleName = $moduleName;
      $request->controllerName = $response->controllerName = $controllerName;
      $request->methodName = $response->methodName = $methodName;
      $controllerClass = action2controllerClass($actionName);
      // controller or method not exists
      if (!class_exists($controllerClass) || !method_exists($controllerClass, $methodName)) { $response->setStatus(404, 'Not Found'); $response->end(); }
      // session store
      if (!APP_SWOOLE) {
        session_set_save_handler(new SessionHandlerFiles(), true);
        session_start();
        $request->_SESSION = $_SESSION;
      }
      // handle
      (new $controllerClass())->$methodName($request, $response);
    } catch (ResponseEndException $e) {
    } catch (Exception $e) {
      $response->write(json_encode_array(APP_DEBUG ? ['errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'errcolumn' => $e->getErrorColumn(), 'errfile' => $e->getErrorFile(), 'errline' => $e->getErrorLine()] : ['errno' => $e->getCode(), 'errmsg' => $e->getMessage(), 'errcolumn' => $e->getErrorColumn()]));
    }
    $response->sendResponse();
    return $this;
  }

  private function initSql() : void {
    Sql::$__host     = envstr('APP_DB_HOST',     '127.0.0.1'); // 默认主机
    Sql::$__port     = envstr('APP_DB_PORT',     '3306');      // 默认端口
    Sql::$__username = envstr('APP_DB_USERNAME', 'root');      // 默认账户
    Sql::$__password = envstr('APP_DB_PASSWORD', 'root');      // 默认密码
    Sql::$__dbname   = envstr('APP_DB_DBNAME',   'zay');       // 默认数据库名称
    Sql::$__charset  = envstr('APP_DB_CHARSET',  'utf8mb4');   // 默认字符集
    Sql::$__prefix   = envstr('APP_DB_PREFIX',   '');          // 默认字符集
  }

  private function initModel() : void {
    // coding
  }

  private function initView() : void {
    // $view = View::getInstance();
  }

  private function initVerify() : void {
    // $verify = Verify::getInstance();
  }

  public function init() : void {
    $this->initSql();
    $this->initModel();
    $this->initVerify();
    $this->initView();
  }

  public function start() : static {
    if (empty($this->modules)) { throw new \LogicException('No modules found! please call enableModules method.'); }
    if (APP_SWOOLE) {
      \Swoole\Runtime::enableCoroutine();
      \Swoole\Coroutine\run(function() {
        $server = new \Swoole\Coroutine\Http\Server(APP_SWOOLE_HOST, APP_SWOOLE_PORT, false);
        $server->handle('/favicon.ico', function($req, $res) {
          $res->status(404);
        });
        $server->handle('/', function($req, $res) {
          $this->handle(Request::fromSwooleHttpRequest($req), Response::fromSwooleHttpResponse($res));
        });
        $server->start();
      });
      return $this;
    } else {
      return $this->handle(Request::fromFpmRequest(), Response::fromFpmResponse());
    }
  }
}
