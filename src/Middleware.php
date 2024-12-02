<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use zay\Request;
use zay\Response;

abstract class Middleware {

  public App $app;

  public Module $module;

  public Controller $controller;

  abstract public function handleRequest(Request $request, Response $response) : void;

  abstract public function handleResponse(Request $request, Response $response) : void;
}
