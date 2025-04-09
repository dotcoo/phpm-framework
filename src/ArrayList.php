<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework;

use ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable, Traversable, ArrayIterator, Closure, SplQueue;

use net\phpm\framework\interfaces\StateInterface;

final class ArrayList implements ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable, StateInterface {
  public static function isArrayList(mixed $obj) : bool {
    return is_a($obj, static::class);
  }

  public static function split(string $separator, ?string $string) : static {
    return new static(empty($string) ? [] : explode($separator, $string));
  }

  public static function explode(string $separator, ?string $string) : static {
    return static::split($separator, $string);
  }

  public static function new(array $array = []) : static {
    return new static($array);
  }

  private array $___data = [];

  public function __construct(array $array = []) {
    $this->___data = $array;
  }

  public function copy() {
    return new static($this->___data);
  }

  public function offsetExists(mixed $offset) : bool {
    return isset($this->___data[$offset]);
  }

  public function offsetGet(mixed $offset) : mixed {
    return $this->___data[$offset];
  }

  public function offsetSet(mixed $offset, mixed $value) : void {
    $this->___data[$offset] = $value;
  }

  public function offsetUnset(mixed $offset) : void {
    unset($this->___data[$offset]);
  }

  public function count() : int {
    return count($this->___data);
  }

  public function getIterator() : Traversable {
    return new ArrayIterator($this->___data);
  }

  public function serialize() : ?string {
    return serialize($this->___data);
  }

  public function unserialize(string $serialized) : void {
    $this->___data = unserialize($serialized);
  }

  public function jsonSerialize() : mixed {
    return $this->___data;
  }

  public function toArray() : array {
    return $this->___data;
  }

  public function empty() : bool {
    return empty($this->___data);
  }

  public function shift() : mixed {
    return array_shift($this->___data);
  }

  public function unshift(mixed ...$args) : static {
    array_unshift($this->___data, ...$args); return $this;
  }

  public function pop() : mixed {
    return array_pop($this->___data);
  }

  public function push(mixed ...$args) : static {
    array_push($this->___data, ...$args); return $this;
  }

  public function first(mixed $defval = null) : mixed {
    return $this->count() === 0 ? $defval : $this->___data[0];
  }

  public function last(mixed $defval = null) : mixed {
    return $this->count() === 0 ? $defval : $this->___data[$this->count() - 1];
  }

  public function at(int $i) : mixed {
    return $i >= 0 ? $this->___data[$i] : $this->___data[$this->count() + $i];
  }

  public function join(string $separator = ',') : string {
    return implode($separator, $this->___data);
  }

  public function implode(string $separator = ',') : string {
    return $this->join($separator);
  }

  public function concat(mixed ...$args) : static {
    array_push($this->___data, ...$args); return $this;
  }

  public function reverse() : static {
    return new static(array_reverse($this->___data));
  }

  public function slice(int $begin, ?int $end = null) : static {
    return new static(array_slice($this->___data, $begin, $end === null ? null : $end - $begin));
  }

  public function splice(int $offset, ?int $deleteCount = null, mixed ...$args) : static {
    return new static(array_splice($this->___data, $offset, $deleteCount === null ? $this->count() : $deleteCount, $args));
  }

  public function sort(Closure $callback) : static {
    usort($this->___data, $callback); return $this;
  }

  public function includes(mixed $val, bool $strict = true) : bool {
    return in_array($val, $this->___data, $strict);
  }

  public function indexOf(mixed $val, bool $strict = true) : int {
    $i = array_search($val, $this->___data, $strict); return $i === false ? -1 : $i;
  }

  public function lastIndexOf(mixed $val, bool $strict = true) : int {
    $i = array_search($val, array_reverse($this->___data), $strict); return $i === false ? -1 : $i;
  }

  public function pad(int $size, mixed $value) : static {
    $this->___data = array_pad($this->___data, $size, $value); return $this;
  }

  public function intersect(array $arr) : static {
    return new static(array_intersect($this->___data, $arr));
  }

  public function diff(array $arr) : static {
    return new static(array_diff($this->___data, $arr));
  }

  public function unique(int $sort_flags = SORT_STRING) : static {
    $this->___data = array_unique($this->___data, $sort_flags); return $this;
  }

  public function shuffle() : static {
    shuffle($this->___data); return $this;
  }

  public function rand(mixed $defval = null) : mixed {
    return $this->count() === 0 ? $defval : $this->___data[array_rand($this->___data)];
  }

  public function rands(int $num = 1) : static {
    if ($num < 1) {
      return new static();
    } elseif ($num === 1) {
      return new static([$this->___data[array_rand($this->___data)]]);
    } elseif ($num >= $this->count()) {
      return new static($this->___data);
    } else {
      $l = new static();
      foreach (array_rand($this->___data, $num) as $k) {
        $l->push($this->___data[$k]);
      }
      return $l;
    }
  }

  public function reduce(Closure $callback, mixed $initial = 0) : mixed {
    $a = $initial;
    foreach ($this->___data as $i => $v) {
      $a = $callback($a, $v, $i, $this->___data);
    }
    return $a;
  }

  public function reduceRight(Closure $callback, mixed $initial = 0) : mixed {
    $a = $initial;
    foreach (array_reverse($this->___data) as $i => $v) {
      $a = $callback($a, $v, $i, $this->___data);
    }
    return $a;
  }

  public function find(Closure $callback, mixed $defval = null) : mixed {
    foreach ($this->___data as $i => $v) {
      if ($callback($v, $i, $this->___data)) {
        return $v;
      }
    }
    return $defval;
  }

  public function findIndex(Closure $callback) : int {
    foreach ($this->___data as $i => $v) {
      if ($callback($v, $i, $this->___data)) {
        return $i;
      }
    }
    return -1;
  }

  public function every(Closure $callback) : bool {
    foreach ($this->___data as $i => $v) {
      if (!$callback($v, $i, $this->___data)) {
        return false;
      }
    }
    return true;
  }

  public function some(Closure $callback) : bool {
    foreach ($this->___data as $i => $v) {
      if ($callback($v, $i, $this->___data)) {
        return true;
      }
    }
    return false;
  }

  public function each(Closure $callback) : static {
    foreach ($this->___data as $i => &$v) {
      $callback($v, $i, $this->___data);
    }
    unset($v);
    return $this;
  }

  public function map(Closure $callback) : static {
    $l = new static();
    foreach ($this->___data as $i => $v) {
      $l->push($callback($v, $i, $this->___data));
    }
    return $l;
  }

  public function filter(Closure $callback) : static {
    $l = new static();
    foreach ($this->___data as $i => $v) {
      if ($callback($v, $i, $this->___data)) {
        $l->push($v);
      }
    }
    return $l;
  }

  public function flat() {
    $list = [];
    $stacks = new SplQueue();
    $stacks->enqueue([$this->___data, 0]);
    while (!$stacks->isEmpty()) {
      [$arr, $i] = $stacks->dequeue();
      for (; $i < count($arr); $i++) {
        $item = $arr[$i];
        if (is_array($item)) {
          $i++;
          $stacks->enqueue([$arr, $i]);
          $stacks->enqueue([$item, 0]);
        } else {
          array_push($list, $item);
        }
      }
    }
    return $list;
  }

  public function relations(string|Closure $prop, string|Closure $fk, string|Closure $class, string $pk = 'id', string ...$columns) : static {
    if ($this->empty()) { return $this; }
    $setProp = gettype($prop) == 'string' ? fn($v, $obj) => $v->$prop = $obj : $prop;
    $getFk = gettype($fk) == 'string' ? fn($v) => $v->$fk : $fk;
    $getPk = gettype($pk) == 'string' ? fn($v) => [$v->$pk, $v] : $pk;
    $selectAll = gettype($class) == 'string' ? fn($ids) => $class::columns(...$columns)->where("`$pk` in (?)", $ids)->selectAll()->toMap($getPk) : $class;
    $map = $selectAll($this->map($getFk)->unique()->toArray());
    foreach($this->___data as $row) {
      $setProp($row, $map[$getFk($row)] ?? null);
    }
    return $this;
  }

  public function toColumn(Closure $callback) : array {
    $map = [];
    foreach ($this->___data as $i => $v) {
      array_push($map, $callback($v, $i, $this->___data));
    }
    return $map;
  }

  public function toMap(Closure $callback) : array {
    $map = [];
    foreach ($this->___data as $i => $v) {
      [$key, $val] = $callback($v, $i, $this->___data);
      $map[$key] = $val;
    }
    return $map;
  }

  public function toGroup(Closure $callback) : array {
    $map = [];
    foreach ($this->___data as $i => $v) {
      [$key, $val] = $callback($v, $i, $this->___data);
      if (array_key_exists($key, $map)) {
        $map[$key]->push($val);
      } else {
        $map[$key] = new static([$val]);
      }
    }
    return $map;
  }

  public function toTree0(?object $parent = null, mixed $pid = 0, string $pidKey = 'pid', string $idKey = 'id', int $level = 0) : static {
    $tree = new static();
    foreach ($this->___data as $v) {
      if ($v->$pidKey !== $pid) { continue; }
      $v->level = $level;
      $v->parent = $parent;
      $v->children = $this->toTree0($v, $v->$idKey, $pidKey, $idKey, $level + 1);
      $tree->push($v);
    }
    return $tree;
  }

  public function toTree(?object $parent = null, mixed $pid = 0, string $pidKey = 'pid', string $idKey = 'id', int $level = 0) : static {
    $parents = $this->toMap(fn($v) => [$v->$idKey, $v]);
    $childrens = $this->toGroup(fn($v) => [$v->$pidKey, $v]);
    $tree = new static();
    foreach ($this->___data as $v) {
      $v->level = null;
      $v->parent = $parents[$v->$pidKey] ?? null;
      $v->children = $childrens[$v->$idKey] ?? new static();
      if ($v->$pidKey !== $pid) { continue; }
      $v->level = $level;
      $tree->push($v);
    }
    $tree->eachTree(fn($v) => $v->level = $v->level !== null ? $v->level : $v->parent->level + 1);
    return $tree;
  }

  public function eachTree(Closure $callback) : static {
    foreach ($this->___data as $i => $v) {
      $callback($v, $i, $this->___data);
      $v->children = $v->children ? $v->children : new static();
      $v->children->eachTree($callback);
    }
    return $this;
  }

  public function mapTree(Closure $callback) : static {
    $tree = new static();
    foreach ($this->___data as $i => $v) {
      $v = $callback($v, $i, $this->___data);
      $v->children = $v->children ? $v->children->mapTree($callback) : new static();
      $tree->push($v);
    }
    return $tree;
  }

  public function __getState() : array {
    return ['___data' => $this->___data];
  }

  public static function __setState(array $data) : static {
    return new static($data['___data']);
  }

  public function __serialize() : array {
    return ['___data' => $this->___data];
  }

  public function __unserialize(array $data) : void {
    $this->___data = $data['___data'];
  }
}
