<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework\interfaces;

use net\phpm\framework\Request;
use net\phpm\framework\Response;

interface MiddlewareInterface {
  public function handleRequest(Request $request, Response $response) : void;
  public function handleResponse(Request $request, Response $response) : void;
}
