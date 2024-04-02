<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\traits;

use Closure;
use zay\ArrayList;
use zay\Sql;

trait ModelTree {
  public ?ArrayList $_list = null;

  public int $_tlevel = -1;

  public bool $_detach = false;

  // public ?self|false $_tparent = false; // PHP 8.2
  public ?self $_tparent = null;

  // public ?ArrayList|false $_children = false; // PHP 8.2
  public ?ArrayList $_children = null;

  public function getTpks() : array {
    return $this->getPks();
  }

  public function getTids() : array {
    $ids = [];
    foreach ($this->getTpks() as $pk) {
      $ids[] = $this->___props[$pk];
    }
    return $ids;
  }

  public function getTfks() : array {
    return ['pid'];
  }

  public function getTpids() : array {
    $pids = [];
    foreach ($this->getTfks() as $fk) {
      $pids[] = $this->___props[$fk];
    }
    return $pids;
  }

  public function isNode(?self $node) : bool {
    return $node !== null && $this->getTids() === $node->getTids();
  }

  public function setParent(?self $parent) : static {
    $this->_tparent = $parent; return $this;
  }

  protected function tempty(array $pids) : bool {
    if (empty($pids)) { return true; }
    foreach ($pids as $pid) { if (empty($pid)) { return true; } }
    return false;
  }

  protected function twhere(array $columns, array $values) : Sql {
    $sql = static::newSql();
    foreach ($columns as $i => $column) {
      $value = $values[$i];
      $sql->where("`$column` = ?", $value);
    }
    return $sql;
  }

  public function getParent(string ...$columns) : ?static {
    if ($this->_tparent !== false) {
      return $this->_tparent;
    } elseif ($this->tempty($this->getTpids())) {
      return null;
    } else {
      $this->_tparent = $this->twhere($this->getTpks(), $this->getTpids())->columns(...$columns)->select();
      if ($this->_tparent !== null) { $this->_tparent->appendChild($this); }
      return $this->_tparent;
    }
  }

  public function isParent(?self $parent) : bool {
    return $parent !== null && $this->getTpids() === $parent->getTids();
  }

  public function getParents(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList {
    $parents = new ArrayList($self ? [$this] : []);
    for ($i = 0, $p = $this->getParent(); $i < $distance && $p !== null; $i++) {
      $parents->push($p);
      $p = $p->getParent();
    }
    return $parents;
  }

  public function isParents(?self $parent, int $distance = PHP_INT_MAX) : bool {
    for ($i = 0, $p = $this->getParent(); $i < $distance && $p !== null; $i++) {
      if ($p->isNode($parent)) { return true; }
      $p = $p->getParent();
    }
    return false;
  }

  public function setChildren(self ...$children) : static {
    $this->_children === new ArrayList($children); return $this;
  }

  public function appendChild(self ...$children) : static {
    $this->getChildren()->push(...$children); return $this;
  }

  public function getChildren() : ArrayList {
    return $this->_children !== false ? $this->_children : $this->_children = $this->twhere($this->getTfks(), $this->getTids())->selectAll()->each(fn($v) => $v->setParent($this));
  }

  public function getChildrenCount() : int {
    return $this->getChildren()->count();
  }

  public function getDescendants(bool $self = false, int $distance = PHP_INT_MAX) : ArrayList {
    --$distance;
    $descendants = new ArrayList($self ? [$this, ...$this->getChildren()->toArray()] : [...$this->getChildren()->toArray()]);
    if ($distance === 0) { return $descendants; }
    foreach ($this->getChildren() as $c) {
      $descendants->push(...$c->getDescendants(false, $distance));
    }
    return $descendants;
  }

  public function getDescendantsCount(bool $self = false, int $distance = PHP_INT_MAX) : int {
    --$distance;
    $count = $self ? 1 + $this->getChildrenCount() : $this->getChildrenCount();
    if ($distance === 0) { return $count; }
    foreach ($this->getChildren() as $c) {
      $count += $c->getDescendantsCount(false, $distance);
    }
    return $count;
  }

  public function isDescendants(self $node, bool $self = false, int $distance = PHP_INT_MAX) : bool {
    --$distance;
    if ($self && $this->isNode($node)) { return true; }
    foreach ($this->getChildren() as $c) {
      if ($c->isNode($node)) { return true; }
      if ($distance === 0) { continue; }
      if ($c->isDescendants($node, false, $distance)) { return true; }
    }
    return false;
  }

  public function getLevel(int $level = 1) : ArrayList {
    --$level;
    if ($level === 0) { return $this->getChildren(); }
    $descendants = new ArrayList();
    foreach ($this->getChildren() as $c) {
      $descendants->push(...$c->getLevel($level));
    }
    return $descendants;
  }

  public function getLevelCount(int $level = 1) : int {
    --$level;
    if ($level === 0) { return $this->getChildrenCount(); }
    $count = 0;
    foreach ($this->getChildren() as $c) {
      $count += $c->getLevelCount($level);
    }
    return $count;
  }

  public function each(Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : static {
    --$distance;
    if ($self) { $func($this); }
    foreach ($this->getChildren() as $c) {
      $func($c);
      if ($distance !== 0) { $c->each($func, false, $distance); }
    }
    return $this;
  }

  public function eachReverse(Closure $func, bool $self = false, int $distance = PHP_INT_MAX) : static {
    --$distance;
    foreach ($this->getChildren()->reverse() as $c) {
      if ($distance !== 0) { $c->eachReverse($func, false, $distance); }
      $func($c);
    }
    if ($self) { $func($this); }
    return $this;
  }

  public function map(Closure $func, bool $self = true, int $distance = PHP_INT_MAX) : array {
    --$distance;
    $children = [];
    foreach ($this->getChildren() as $c) {
      $children[] = $func($c, $distance !== 0 ? $c->map($func, false, $distance) : []);
    }
    return $self ? $func($this, $children) : $children;
  }

  public static function getTree(ArrayList $list = null) : static {
    return static::root()->parseTree($list ?? static::order('`ord` DESC')->selectAll());
  }

  public function parseTree(?ArrayList $list, $detach = true) : static {
    $this->_tparent = null;
    $this->_children = new ArrayList();
    $this->_list = new ArrayList();
    if ($list === null) { return $this; }
    $this->_list = $list;
    $parents = $list->toMap(fn($v) => [implode(',', $v->getTids()), $v]);
    $childrens = $list->toGroup(fn($v) => [implode(',', $v->getTpids()), $v]);
    $root = $this;
    foreach ($list as $v) {
      $v->_tparent = $parents[implode(',', $v->getTpids())] ?? $root;
      $v->_children = $childrens[implode(',', $v->getTids())] ?? new ArrayList();
      $v->_tlevel = $root->_tlevel + 1;
      if (!$v->isParent($root)) { continue; }
      $root->appendChild($v);
    }
    return $root->parseLevel()->detach($detach);
  }

  public function showChildren() : static {
    $this->___props['children'] = $this->_children;
    foreach ($this->_children as $c) {
      $c->showChildren();
    }
    return $this;
  }

  public function parseLevel() : static {
    return $this->each(fn($v) => $v->_tlevel = $v->getParent()->_tlevel + 1);
  }

  public function findTree(Closure $func, $defval = null) : ?static {
    return $this->_list->find($func, $defval);
  }

  public function findNode(int $id, $defval = null) : static {
    return $this->_list->find(fn($v) => $v->getTids()[0] === $id, $defval);
  }

  public function detach(bool $detach = true) : static {
    $this->_detach = $detach;
    $this->getChildren()->each(fn($v) => $v->setParent($detach ? null : $this));
    return $this;
  }

  public function __getState() : array {
    return ['___props' => $this->___props, '_detach' => $this->_detach, '_list' => $this->_list];
  }

  public static function __setState(array $data) : static {
    return static::new($data['___props'])->ignoreChange()->parseTree($data['_list'], $data['_detach']);
  }

  public function __serialize() : array {
    return ['___props' => $this->___props, '_detach' => $this->_detach, '_list' => $this->_list];
  }

  public function __unserialize(array $data) : void {
    $this->___props = $data['___props'];
    $this->ignoreChange()->parseTree($data['_list'], $data['_detach']);
  }

  public function addTop(array $top = []) : static {
    $node = static::new(array_merge($top, ['id' => 0, 'pid' => -1, 'name' => 'ROOT']));
    $this->list->unshift($node);
    $this->getChildren()->unshift($node);
    return $this;
  }

  public static function root(array $root = []) : static {
    return static::new(array_merge($root, ['id' => 0, 'pid' => -1, 'name' => 'ROOT']));
  }
}
