<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\middlewares;

use zay\Request;
use zay\Response;
use zay\Middleware;

class CorsMiddleware extends Middleware {
  public function handleRequest(Request $request, Response $response) : void {
    $module = $request->module;
    $allowOrigin = $module->config('cors.allowOrigin', '*');
    $origin = $request->getHeader('Origin');
    $allowOrigins = array_map('trim', explode2(',', $allowOrigin));
    if (!($allowOrigin === '*' || in_array($origin, $allowOrigins))) {
      $response->error('reject');
      return;
    }
    $response->header('Access-Control-Allow-Origin', $origin);
    $response->header('Vary', 'Origin');
    $response->header('Access-Control-Allow-Methods', $module->config('cors.allowMethods', 'GET, POST'));
    $response->header('Access-Control-Allow-Headers', $module->config('cors.allowHeaders', 'Content-Type, Accept'));
    $response->header('Access-Control-Allow-Credentials', 'true');
    $response->header('Access-Control-Expose-Headers', $module->config('cors.exposeHeaders', 'Set-Token'));
    $response->header('Access-Control-Max-Age', $module->config('cors.maxAge', '600') . '');
    if ($request->method === 'OPTIONS') {
      $response->success('ok');
    }
    return;
  }

  public function handleResponse(Request $request, Response $response) : void {
    return;
  }
}
