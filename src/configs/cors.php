<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);
return [
  'allowOrigin' => '*',
  'allowMethods' => 'OPTIONS, GET, POST, PUT, DELETE',
  'allowHeaders' => 'Content-Type, Accept, Authorization',
  'exposeHeaders' => 'Set-Authorization',
  'maxAge' => '600',
];
