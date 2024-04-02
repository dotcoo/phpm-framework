<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

use zay\ArrayList;

interface TreeInterface {
  public function getTpks() : array;
  public function getTids() : array;
  public function getTfks() : array;
  public function getTpids() : array;
  public function isNode(?self $node) : bool;
  public function getParent() : ?static;
  public function getChildren() : ArrayList;
  public function getChildrenCount() : int;
  public function getDescendants() : ArrayList;
  public function getDescendantsCount() : int;
}
