<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

use zay\ArrayList;

interface ModelTreeInterface {
  public function getTpks() : array;
  public function getTids() : array;
  public function getTfks() : array;
  public function getTpids() : array;
  public function isNode(?ModelTreeInterface $node) : bool;
  public function setParent(?ModelTreeInterface $parent) : ModelTreeInterface;
  public function getParent(string ...$columns) : ?ModelTreeInterface;
  public function isParent(?ModelTreeInterface $parent) : bool;
  public function getParents(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList;
  public function isParents(?ModelTreeInterface $parent, int $distance = PHP_INT_MAX) : bool;
  public function setChildren(ModelTreeInterface ...$children) : ModelTreeInterface;
  public function appendChild(ModelTreeInterface ...$children) : ModelTreeInterface;
  public function getChildren() : ArrayList;
  public function getChildrenCount() : int;
  public function getDescendants(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList;
  public function getDescendantsCount(bool $self = false, int $distance = PHP_INT_MAX) : int;
  public function isDescendants(ModelTreeInterface $node, bool $self = false, int $distance = PHP_INT_MAX) : bool;
  public function getLevel(int $level = 1) : ArrayList;
  public function getLevelCount(int $level = 1) : int;
  public function each(\Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : ModelTreeInterface;
  public function eachReverse(\Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : ModelTreeInterface;
  public function map(\Closure $func, bool $self = true, int $distance = PHP_INT_MAX) : array;
  public function parseTree(?ArrayList $list, $detach = true) : ModelTreeInterface;
  public function showChildren() : ModelTreeInterface;
  public function parseLevel() : ModelTreeInterface;
  public function findTree(\Closure $func, $defval = null) : ?ModelTreeInterface;
  public function findNode(int $id, $defval = null) : ModelTreeInterface;
  public function detach(bool $detach = true) : ModelTreeInterface;
  public function addRoot(array $top = []) : ModelTreeInterface;
}
