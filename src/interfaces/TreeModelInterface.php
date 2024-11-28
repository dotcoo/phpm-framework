<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\interfaces;

use Closure;

use zay\ArrayList;

interface TreeModelInterface {
  // public function getTpks() : array;
  // public function getTids() : array;
  // public function getTfks() : array;
  // public function getTpids() : array;
  // public function isNode(?TreeModelInterface $node) : bool;
  // public function setParent(?TreeModelInterface $parent) : TreeModelInterface;
  // public function getParent(string ...$columns) : ?TreeModelInterface;
  // public function isParent(?TreeModelInterface $parent) : bool;
  // public function getParents(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList;
  // public function isParents(?TreeModelInterface $parent, int $distance = PHP_INT_MAX) : bool;
  // public function setChildren(TreeModelInterface ...$children) : TreeModelInterface;
  // public function appendChild(TreeModelInterface ...$children) : TreeModelInterface;
  // public function getChildren() : ArrayList;
  // public function getChildrenCount() : int;
  // public function getDescendants(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList;
  // public function getDescendantsCount(bool $self = false, int $distance = PHP_INT_MAX) : int;
  // public function isDescendants(TreeModelInterface $node, bool $self = false, int $distance = PHP_INT_MAX) : bool;
  // public function getLevel(int $level = 1) : ArrayList;
  // public function getLevelCount(int $level = 1) : int;
  // public function each(Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : TreeModelInterface;
  // public function eachReverse(Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : TreeModelInterface;
  // public function map(Closure $func, bool $self = true, int $distance = PHP_INT_MAX) : array;
  // public function parseTree(?ArrayList $list, $detach = true) : TreeModelInterface;
  // public function showChildren() : TreeModelInterface;
  // public function parseLevel() : TreeModelInterface;
  // public function findTree(Closure $func, $defval = null) : ?TreeModelInterface;
  // public function findNode(int $id, $defval = null) : TreeModelInterface;
  // public function detach(bool $detach = true) : TreeModelInterface;
  // public function addRoot(array $top = []) : TreeModelInterface;
}
