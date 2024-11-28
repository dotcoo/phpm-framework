<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\traits;

use Closure, BadMethodCallException, Traversable, ArrayIterator;

trait DynamicTrait {
  protected static array $___staticMethods = [];

  public static function setStaticMethod(string $name, Closure $value) : void {
    static::$___staticMethods[$name] = $value;
  }

  public static function unsetStaticMethod(string $name) : void {
    unset(static::$___staticMethods[$name]);
  }

  public static function ___callStatic(string $name, array $args) : mixed {
    $nameStatic = $name . 'Static';
    if (method_exists(static::class, $nameStatic)) {
      return static::$nameStatic(...$args);
    } elseif (array_key_exists($name, static::$___staticMethods)) {
      return static::$___staticMethods[$name]->call(null, ...$args);
    } else {
      return (new static())->$name(...$args);
    }
  }

  public static function __callStatic(string $name, array $args) : mixed {
    return static::___callStatic($name, $args);
  }

  protected static array $___objectMethods = [];

  public static function setObjectMethod(string $name, Closure $value) : void {
    static::$___objectMethods[$name] = $value;
  }

  public static function unsetObjectMethod(string $name) : void {
    unset(static::$___objectMethods[$name]);
  }

  protected array $___props = [];

  protected array $___methods = [];

  public function ___set(string $name, mixed $value) : void {
    if ($value instanceof Closure) { $this->___methods[$name] = $value; return; }
    $setMethodName = 'set' . camel2pascal($name);
    if (method_exists($this, $setMethodName)) {
      $this->$setMethodName($value);
    } elseif (array_key_exists($setMethodName, $this->___methods)) {
      $this->___methods[$setMethodName]->call($this, $value);
    } elseif (array_key_exists($setMethodName, static::$___objectMethods)) {
      static::$___objectMethods[$setMethodName]->call($this, $value);
    } else {
      $this->___props[$name] = $value;
    }
  }

  public function __set(string $name, mixed $value) : void {
    $this->___set($name, $value);
  }

  public function ___get(string $name) : mixed {
    $getMethodName = 'get' . camel2pascal($name);
    if (method_exists($this, $getMethodName)) {
      return $this->$getMethodName();
    } elseif (array_key_exists($getMethodName, $this->___methods)) {
      return $this->___methods[$getMethodName]->call($this);
    } elseif (array_key_exists($getMethodName, static::$___objectMethods)) {
      return static::$___objectMethods[$getMethodName]->call($this);
    } else {
      return $this->___props[$name] ?? null;
    }
  }

  public function __get(string $name) : mixed {
    return $this->___get($name);
  }

  public function ___call(string $name, array $args) : mixed {
    if (array_key_exists($name, $this->___methods)) {
      return $this->___methods[$name]->call($this, ...$args);
    } elseif (array_key_exists($name, static::$___objectMethods)) {
      return static::$___objectMethods[$name]->call($this, ...$args);
    }
    throw new BadMethodCallException(static::class . "->$name method not found!");
  }

  public function __call(string $name, array $args) : mixed {
    return $this->___call($name, $args);
  }

  public function ___isset(string $name) : bool {
    return array_key_exists($name, $this->___props);
  }

  public function __isset(string $name) : bool {
    return $this->___isset($name);
  }

  public function ___unset(string $name): void {
    unset($this->___props, $name);
    unset($this->___methods, $name);
  }

  public function __unset(string $name): void {
    $this->___unset($name);
  }

  public function offsetExists(mixed $offset) : bool {
    return $this->___isset($offset);
  }

  public function offsetSet(mixed $offset, mixed $value) : void {
    $this->___props[$offset] = $value;
  }

  public function offsetGet(mixed $offset) : mixed {
    return $this->___props[$offset];
  }

  public function offsetUnset(mixed $offset) : void {
    $this->___unset($offset);
  }

  public function count() : int {
    return count($this->___props);
  }

  public function getIterator() : Traversable {
    return new ArrayIterator($this->___props);
  }

  public function serialize() : ?string {
    return serialize($this->___props);
  }

  public function unserialize(string $serialized) : void {
    $this->___props = unserialize($serialized);
  }

  public function __serialize() : array {
    return ['___props' => $this->___props];
  }

  public function __unserialize(array $data) : void {
    $this->___props = $data['___props'];
  }

  public static array $___jsonIgnoreColumns = [];

  public function jsonSerialize() : mixed {
    $json = [];
    foreach($this->___props as $name => $value) {
      if (str_starts_with($name, '_') || in_array($name, static::$___jsonIgnoreColumns)) { continue; }
      $json[$name] = $this->___props[$name];
    }
    return $json;
  }

  public function toArray() : array {
    return $this->___props;
  }

  public function __toString() {
    return json_encode_object($this);
  }
}
