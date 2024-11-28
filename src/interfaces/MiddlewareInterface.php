<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

use openmall\Request;
use openmall\Response;

interface MiddlewareInterface {
  public function handleRequest(Request $request, Response $response) : array;
  public function handleResponse(Request $request, Response $response) : array;
}
