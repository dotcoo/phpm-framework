<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework\traits;

use Closure, BadMethodCallException, Traversable, ArrayIterator;

trait DynamicTrait {
  protected static array $___callStaticMethods = [];

  public static function setCallStaticMethod(string $name, Closure $value) : void {
    static::$___callStaticMethods[$name] = $value;
  }

  public static function unsetCallStaticMethod(string $name) : void {
    unset(static::$___callStaticMethods[$name]);
  }

  protected static function ___callStatic(string $name, array $args) : mixed {
    $nameStatic = $name . 'CallStatic';
    if (method_exists(static::class, $nameStatic)) {
      return static::$nameStatic(...$args);
    } elseif (array_key_exists($name, static::$___callStaticMethods)) {
      return static::$___callStaticMethods[$name]->call(null, ...$args);
    } else {
      return (new static())->___call($name, $args);
    }
  }

  public static function __callStatic(string $name, array $args) : mixed {
    return static::___callStatic($name, $args);
  }

  protected static array $___callMethods = [];

  public static function setCallMethod(string $name, Closure $value) : void {
    static::$___callMethods[$name] = $value;
  }

  public static function unsetCallMethod(string $name) : void {
    unset(static::$___callMethods[$name]);
  }

  protected function ___call(string $name, array $args) : mixed {
    $nameMethod = $name.'Call';
    if (method_exists($this, $nameMethod)) {
      $this->$nameMethod(...$args);
    } elseif (array_key_exists($name, static::$___callMethods)) {
      return static::$___callMethods[$name]->call($this, ...$args);
    } else {
      throw new BadMethodCallException(static::class . "->$name method not found!");
    }
  }

  public function __call(string $name, array $args) : mixed {
    return $this->___call($name, $args);
  }

  protected array $___props = [];

  protected function ___set(string $name, mixed $value) : void {
    $setMethodName = 'set' . camel2pascal($name);
    if (method_exists($this, $setMethodName)) {
      $this->$setMethodName($value);
    } elseif (array_key_exists($setMethodName, static::$___callMethods)) {
      static::$___callMethods[$setMethodName]->call($this, $value);
    } else {
      $this->___props[$name] = $value;
    }
  }

  public function __set(string $name, mixed $value) : void {
    $this->___set($name, $value);
  }

  protected function ___get(string $name) : mixed {
    $getMethodName = 'get' . camel2pascal($name);
    if (method_exists($this, $getMethodName)) {
      return $this->$getMethodName();
    } elseif (array_key_exists($getMethodName, static::$___callMethods)) {
      return static::$___callMethods[$getMethodName]->call($this);
    } else {
      return $this->___props[$name] ?? null;
    }
  }

  public function __get(string $name) : mixed {
    return $this->___get($name);
  }

  protected function ___isset(string $name) : bool {
    return isset($this->___props[$name]);
  }

  public function __isset(string $name) : bool {
    return $this->___isset($name);
  }

  protected function ___unset(string $name): void {
    unset($this->___props, $name);
  }

  public function __unset(string $name): void {
    $this->___unset($name);
  }

  public function offsetExists(mixed $offset) : bool {
    return array_key_exists($offset, $this->___props);
  }

  public function offsetSet(mixed $offset, mixed $value) : void {
    $this->___props[$offset] = $value;
  }

  public function offsetGet(mixed $offset) : mixed {
    return $this->___props[$offset] ?? null;
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

  public function serialize() : string {
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

  protected static array $___jsonSerializeIgnore = [];

  public function jsonSerialize() : mixed {
    $json = [];
    foreach($this->___props as $name => $value) {
      if (str_starts_with($name, '_') || in_array($name, static::$___jsonSerializeIgnore)) { continue; }
      $json[$name] = $value;
    }
    return $json;
  }

  public function toArray() : array {
    return $this->___props;
  }

  public function __toString() {
    return json_encode_object($this->___props);
  }
}
