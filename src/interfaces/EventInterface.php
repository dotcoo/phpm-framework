<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

use Closure;

interface EventInterface {
  public function addEventListener(string $name, Closure $handle) : static;
  public function removeEventListener(string $name, Closure $handle) : static;
  public function dispatchEvent(string $name, mixed ...$args) : static;
}
