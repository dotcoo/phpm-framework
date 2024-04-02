<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

use zay\Request;
use zay\Response;

interface HandlerInterface {
  public function handle(Request $request, Response $response) : void;
}
