<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use Closure;
use zay\Request;
use zay\Response;

final class Handler {

  public ?App $app = null;

  public ?Module $module = null;

  public ?Controller $controller = null;

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
    // TODO 中间件
    // $this->method->call(null, [$request, $response]);
    ($this->method)($request, $response);
  }
}
