<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\traits;

trait Singleton {
  private static ?self $instance = null;

  public static function getInstance() : static {
    return static::$instance ?? static::$instance = new static();
  }

  private function __construct() {
    $this->init();
  }
}
