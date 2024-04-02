<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\traits;

trait EventCenter {
  protected static array $___classEvents = [];

  public static function addClassEventListener(string $name, \Closure $handle) : void {
    if (!array_key_exists($name, static::$___classEvents)) { static::$___classEvents[$name] = []; }
    static::$___classEvents[$name][] = $handle;
  }

  public static function removeClassEventListener(string $name, \Closure $handle) : void {
    if (!array_key_exists($name, static::$___classEvents)) { return; }
    static::$___classEvents[$name] = array_filter(fn($v) => $v !== $handle, static::$___classEvents[$name]);
  }

  public static function dispatchClassEvent(string $name, mixed ...$args) : void {
    $eventMethodName = 'on' . camel2pascal($name);
    if (method_exists($this, $eventMethodName)) {
      static::$eventMethodName(...$args);
    }
    if (array_key_exists($name, static::$___classEvents)) {
      foreach (static::$___classEvents[$name] as $event) { $event->call(null, ...$args); }
    }
  }

  protected array $___events = [];

  public function addEventListener(string $name, \Closure $handle) : static {
    if (!array_key_exists($name, $this->___events)) { $this->___events[$name] = []; }
    $this->___events[$name][] = $handle;
    return $this;
  }

  public function removeEventListener(string $name, \Closure $handle) : static {
    if (!array_key_exists($name, $this->___events)) { return $this; }
    $this->___events[$name] = array_filter(fn($v) => $v !== $handle, $this->___events[$name]);
    return $this;
  }

  public function dispatchEvent(string $name, mixed ...$args) : static {
    $eventMethodName = 'on' . camel2pascal($name);
    if (method_exists($this, $eventMethodName)) {
      $this->$eventMethodName(...$args);
    }
    if (array_key_exists($name, $this->___events)) {
      foreach ($this->___events[$name] as $event) { $event->call($this, ...$args); }
    }
    return $this;
  }
}
