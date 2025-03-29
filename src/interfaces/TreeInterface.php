<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace net\phpm\framework\interfaces;

use Closure;

use net\phpm\framework\ArrayList;

interface TreeInterface {
  public function getTpks() : array;
  public function getTids() : array;
  public function getTfks() : array;
  public function getTpids() : array;
  public function isNode(?TreeInterface $node) : bool;
  public function setParent(?TreeInterface $parent) : TreeInterface;
  public function getParent(string ...$columns) : ?TreeInterface;
  public function isParent(?TreeInterface $parent) : bool;
  public function getParents(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList;
  public function isParents(?TreeInterface $parent, int $distance = PHP_INT_MAX) : bool;
  public function setChildren(TreeInterface ...$children) : TreeInterface;
  public function appendChild(TreeInterface ...$children) : TreeInterface;
  public function getChildren() : ArrayList;
  public function getChildrenCount() : int;
  public function getDescendants(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList;
  public function getDescendantsCount(bool $self = false, int $distance = PHP_INT_MAX) : int;
  public function isDescendants(TreeInterface $node, bool $self = false, int $distance = PHP_INT_MAX) : bool;
  public function getLevel(int $level = 1) : ArrayList;
  public function getLevelCount(int $level = 1) : int;
  public function each(Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : TreeInterface;
  public function eachReverse(Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : TreeInterface;
  public function map(Closure $func, bool $self = true, int $distance = PHP_INT_MAX) : array;
  public function parseTree(?ArrayList $list, $detach = true) : TreeInterface;
  public function showChildren() : TreeInterface;
  public function parseLevel() : TreeInterface;
  public function findTree(Closure $func, $defval = null) : ?TreeInterface;
  public function findNode(int $id, $defval = null) : TreeInterface;
  public function detach(bool $detach = true) : TreeInterface;
  public function addRoot(array $top = []) : TreeInterface;
}
