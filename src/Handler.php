<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use Closure;
use zay\Request;
use zay\Response;

final class Handler {

  public App $app;

  public Module $module;

  public Controller $controller;

  public string $name = '';

  public string $fullname = '';

  public string $url = '';

  public string $fullurl = '';

  public ?Closure $method = null;


  public function handle(Request $request, Response $response) : void {
    $request->app = $response->app = $this->app;
    $request->module = $response->module = $this->module;
    $request->controller = $response->controller = $this->controller;
    $request->handler = $response->handler = $this;

    // 请求中间件
    $middlewares = [];
    $module = $this->module;
    while ($module) {
      array_unshift($middlewares, ...$module->middlewares);
      $module = $module->parent;
    }
    foreach ($middlewares as $middleware) {
      $middleware->handleRequest($request, $response);
    }

    // 处理
    // $this->method->call(null, [$request, $response]);
    ($this->method)($request, $response);

    // 响应中间件
    foreach (array_reverse($middlewares) as $middleware) {
      $middleware->handleResponse($request, $response);
    }
  }
}
