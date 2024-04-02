<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

namespace zay;

use zay\interfaces\ModuleInterface;
use zay\Verify;

abstract class Module {
  public function getNamespace() : string {
    return class2package(static::class);
  }

  public function getName() : string {
    return class2class(class2package(static::class));
  }

  public function getUrl() : string {
    return class2class(class2package(static::class));
  }

  public function getPath() : string {
    return class2dir(static::class);
  }

  public function init() : void {

  }
}
