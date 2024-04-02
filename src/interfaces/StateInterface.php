<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

interface StateInterface {
  public function __getState() : array;

  public static function __setState(array $state) : static;
}
