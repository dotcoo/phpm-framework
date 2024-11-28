<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

abstract class Controller {

  public ?App $app = null;

  public ?Module $module = null;

  public string $name = '';

  public string $fullname = '';

  public string $url = '';

  public string $fullurl = '';
}
