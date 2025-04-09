<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework;

use Closure, ReflectionClass, ReflectionObject, ReflectionMethod, LogicException;

final class Module {

  public App $app;

  public self $root;

  public ?self $parent = null;

  public string $path = '';

  public string $namespace = '';

  public ?string $name = null;

  public string $fullname = '';

  public ?string $url = null;

  public string $fullurl = '';

  public array $configs = [];

  public array $config = [];

  public array $middlewares = [];

  public array $modules = [];

  public function load(string ...$moduleNames) {
    $this->loadConfigs();
    $this->loadConfig();
    $this->loadHelpers();
    $this->loadMiddlewares();
    $this->loadControllers();
    $this->loadRoutes();
    $this->loadRegexps();
    $this->loadPipes();
    $this->loadViews();
    $this->loadCommands();
    $this->loadModules(...$moduleNames);
  }

  private function loadConfigs() : void {
    $module = $this;
    foreach (scanfile0($module->path.'/configs') as $file) {
      if (!str_ends_with($file, '.php')) { continue; }
      $path = $module->path.'/configs/'.$file;
      $default = __DIR__.'/configs/'.$file;
      $module->configs[substr($file, 0, -4)] = file_exists($default) ? array_merge(require $default, require $path) : require $path;
    }
  }

  public function config(string $name, mixed $defval = null) : mixed {
    $config = $this->config;
    foreach (explode('.', $name) as $n) {
      if (!array_key_exists($name, $config)) { return $defval; }
      $config = $config[$n];
    }
    return $config;
  }

  private function loadConfig() : void {
    $module = $this;
    $config = $module->configs['module'] ?? [];
    $module->name = $module->name ?? $config['name'] ?? filebase($module->path);
    $module->fullname = $module->name;
    $module->url = $module->url ?? $config['url'] ?? camel2kebab($module->name);
    $module->fullurl = str_starts_with($module->url, '/') ? $module->url : ($module->parent === null ? '' : $module->parent->fullurl.'/'.$module->url);
    array_push($module->app->modules, $module);
    if (array_key_exists($module->fullname, $module->app->moduleNames)) { throw new LogicException("Duplicate module names: {$module->fullname}! {$module->path}"); }
    $module->app->moduleNames[$module->fullname] = $module;
    $module->app->moduleUrls[$module->fullurl] = $module;
  }

  private function loadHelpers() : void {
    $module = $this;
    foreach (scanfile1("{$module->path}/helpers") as $file) {
      if (!str_ends_with($file, '.php')) { continue; }
      $path = "{$module->path}/helpers/$file";
      require $path;
    }
  }

  private function loadMiddlewares() : void {
    $module = $this;
    $middlewares = $module->configs['middleware'] ?? [];
    foreach ($middlewares as $namespaceClassname) {
      $middleware = new $namespaceClassname();
      $middleware->app = $module->app;
      $middleware->module = $module;
      array_push($module->middlewares, $middleware);
    }
  }

  private function loadControllers() : void {
    if (APP_ENGINE === 'fpm') { return; }
    $module = $this;
    foreach (scanfile0("{$module->path}/controllers") as $file) {
      if (!str_ends_with($file, 'Controller.php')) { continue; }
      $path = $module->path.'/controllers/'.$file;
      $classname = substr($file, 0, -4);
      $namespaceClassname = $module->namespace.'\\controllers\\'.$classname;
      $reflect = new ReflectionClass($namespaceClassname);
      if ($reflect->isAbstract()) { continue; }
      $controller = new $namespaceClassname();
      $controller->app = $module->app;
      $controller->module = $module;
      $controller->name = pascal2camel(substr($classname, 0, -10));
      $controller->fullname = $module->fullname.'/'.$controller->name;
      $controller->url = camel2kebab($controller->name);
      $controller->fullurl = $module->fullurl.'/'.$controller->url;
      $module->app->controllers[$controller->fullname] = $controller;
      $reflect = new ReflectionObject($controller);
      foreach ($reflect->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (str_starts_with($method->name, '_')) { continue; }
        $handle = new Handler();
        $handle->app = $module->app;
        $handle->module = $module;
        $handle->controller = $controller;
        $handle->name = pascal2camel($method->name);
        $handle->fullname = $module->fullname.'/'.$controller->name.'/'.$handle->name;
        $handle->url = camel2kebab($handle->name);
        $handle->fullurl = $controller->fullurl.'/'.$handle->url;
        $handle->method = Closure::fromCallable([$controller, $method->name]);
        $module->app->handles[$handle->fullname] = $handle;
        $module->app->urls[$handle->fullurl] = $handle->fullname;
      }
    }
  }

  private function loadRoutes() : void {
    $module = $this;
    $default = [
      'url'     => '',
      'prefix'  => '',
      'regexp'  => '',
      'handler' => '',
      'get'     => [],
      'post'    => [],
    ];
    $routes = $module->configs['routes'] ?? [];
    foreach ($routes as $route) {
      $route = array_merge($default, $route);
      $route['handler'] = $module->fullname.'/'.$route['handler'];
      array_push($module->app->routes, $route);
    }
  }

  private function loadRegexps() : void {
    $module = $this;
    $regexps = $module->configs['regexp'] ?? [];
    foreach ($regexps as $name => $regexp) {
      Verify::$regexps[$name] = $regexp;
      $module->app->regexps[$name] = $regexp;
    }
  }

  private function loadPipes() : void {
    $module = $this;
    $pipes = $module->configs['pipe'] ?? [];
    foreach ($pipes as $name => $pipe) {
      View::$pipes[$name] = $pipe;
      $module->app->pipes[$name] = $pipe;
    }
  }

  private function loadViews() : void {
    if (APP_ENGINE === 'fpm') { return; }
    $module = $this;
    $sourceDir = $module->path.'/views';
    $targetDir = APP_VIEW.'/'.$module->fullname;
    foreach (scanfile1($sourceDir) as $file) {
      if (!str_ends_with($file, '.view.php')) { continue; }
      $source = $sourceDir.$file;
      $target = substr($targetDir.$file, 0, -9).'.php';
      $handler = '/'.$module->fullname.substr($file, 0, -9);
      View::compile($source, $target, $handler);
      View::$views[$handler] = require $target;
    }
  }

  private function loadCommands() : void {

  }

  private function loadModules(string ...$moduleNames) : void {
    $parent = $this;
    $moduleNames = !empty($moduleNames) ? $moduleNames : scandir0($parent->path.'/modules/');
    foreach ($moduleNames as $moduleName) {
      $path = $parent->path.'/modules/'.$moduleName;
      $module = new static();
      $module->app = $parent->app;
      $module->root = $parent->root;
      $module->parent = $parent;
      $module->path = $path;
      $module->namespace = $parent->namespace.'\\modules\\'.$moduleName;
      $module->load();
    }
  }
}
