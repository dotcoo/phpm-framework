<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework;

use ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable, PDO, BadMethodCallException;

use net\phpm\framework\interfaces\EventInterface;
use net\phpm\framework\interfaces\StateInterface;
use net\phpm\framework\traits\DynamicTrait;
use net\phpm\framework\traits\EventTrait;

abstract class Model implements ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable, EventInterface, StateInterface {

  use DynamicTrait;
  use EventTrait;

  public static function new(array $array = [], bool $ignoreChange = false) : static {
    return (new static())->mergeArray($array, $ignoreChange);
  }

  public function mergeArray(array $array, bool $ignoreChange = false) : static {
    foreach($array as $name => $value) {
      $this->___props[$name] = $value;
      if ($ignoreChange) { continue; }
      $this->___changes[$name] = true;
    }
    return $this;
  }

  protected static array $___record2props = [];

  public function mergeRecord(array $record) : static {
    foreach($record as $name => $value) {
      $this->___props[$name] = array_key_exists($name, static::$___record2props) ? static::$___record2props[$name]($value, $record, $this) : $value;
    }
    return $this;
  }

  protected static array $___prop2records = [];

  public function toRecord() : array {
    $record = [];
    foreach($this->___props as $name => $value) {
      $record[$name] = array_key_exists($name, static::$___prop2records) ? static::$___prop2records[$name]($value, $record, $this) : $value;
    }
    return $record;
  }

  protected array $___changes = [];

  public function __set(string $name, mixed $value) : void {
    $this->___set($name, $value);
    $this->___changes[$name] = true;
  }

  public function isChange() : bool {
    return count($this->___changes) > 0;
  }

  public function ignoreChange(mixed ...$args) : mixed {
    $this->___changes = [];
    return empty($args) ? $this : $args[0];
  }

  public function clone() : static {
    return static::new($this->___props);
  }

  public function __getState() : array {
    return ['___props' => $this->___props];
  }

  public static function __setState(array $data) : static {
    return static::new($data['___props']);
  }

  public static function find(mixed ...$ids) : ?static {
    return static::new()->newSqlCall()->whereByPk(...$ids)->select();
  }

  protected function ___call(string $name, array $args) : mixed {
    $nameMethod = $name.'Call';
    if (method_exists($this, $nameMethod)) {
      $this->$nameMethod(...$args);
    } elseif (array_key_exists($name, static::$___callMethods)) {
      return static::$___callMethods[$name]->call($this, ...$args);
    } else {
      return $this->newSqlCall()->$name(...$args);
    }
  }
  const MODE_DELETE = 1;
  const MODE_MARK = 2;
  const MODE_MARK_DELETE = 3;
  public static ?PDO $conn = null;
  public static ?string $database = null;
  public static ?string $table = null;
  public static ?string $alias = null;
  public static ?array $pks = null;
  public static ?bool $autoIncrement = null;
  public static ?bool $autoTimeColumn = null;
  public static ?int $deleteMode = null;
  protected ?PDO $_conn = null;
  protected ?string $_database = null;
  protected ?string $_table = null;
  protected ?string $_alias = null;
  protected ?array $_pks = null;
  protected ?bool $_autoIncrement = null;
  protected ?bool $_autoTimeColumn = null;
  protected ?int $_deleteMode = null;
  public function setConn(?PDO $conn) : static {
    $this->_conn = $conn; return $this;
  }
  public function getConn() : ?PDO {
    return $this->_conn ?? static::$conn ?? null;
  }
  public function setDatabase(?string $database) : static {
    $this->_database = $database; return $this;
  }
  public function getDatabase() : string {
    return $this->_database ?? static::$database ?? '';
  }
  public function setTable(?string $table) : static {
    $this->_table = $table; return $this;
  }
  public function getTable() : string {
    return env('APP_DB_PREFIX') . ($this->_table ?? static::$table ?? camel2under(pascal2camel(substr(static::class, strrpos(static::class, '\\') + 1))));
  }
  public function setAlias(?string $alias) : static {
    $this->_alias = $alias; return $this;
  }
  public function getAlias() : string {
    return $this->_alias ?? static::$alias ?? '';
  }
  public function setPks(?array $pks) : static {
    $this->_pks = $pks; return $this;
  }
  public function getPks() : array {
    return $this->_pks ?? static::$pks ?? ['id'];
  }
  public function setAutoIncrement(?bool $autoIncrement) : static {
    $this->_autoIncrement = $autoIncrement; return $this;
  }
  public function getAutoIncrement() : bool {
    return $this->_autoIncrement ?? static::$autoIncrement ?? true;
  }
  public function setAutoTimeColumn(?bool $autoTimeColumn) : static {
    $this->_autoTimeColumn = $autoTimeColumn; return $this;
  }
  public function getAutoTimeColumn() : bool {
    return $this->_autoTimeColumn ?? static::$autoTimeColumn ?? true;
  }
  public function setDeleteMode(string $deleteMode) : static {
    $this->_deleteMode = $deleteMode; return $this;
  }
  public function getDeleteMode() : int {
    return $this->_deleteMode ?? static::$deleteMode ?? static::MODE_MARK;
  }
  public function newSqlCall() : Sql {
    return (new Sql())->model($this);
  }
  public function add(string ...$columns) : Sql {
    $columns = !empty($columns) ? $columns : array_keys($this->___changes);
    return $this->ignoreChange($this->newSqlCall()->cols(...$columns)->insert());
  }
  public function edit(string ...$columns) : Sql {
    $columns = !empty($columns) ? $columns : array_keys($this->___changes);
    return $this->ignoreChange($this->newSqlCall()->sets(...array_diff($columns, $this->getPks()))->whereMyPk()->update());
  }
  public function del() : Sql {
    return $this->ignoreChange($this->newSqlCall()->whereMyPk()->delete());
  }
  public function save(string ...$columns) : Sql {
    return $this->exists() ? $this->edit(...$columns) : $this->add(...$columns);
  }
  public function load(string ...$columns) : ?static {
    $model = $this->newSqlCall()->columns(...$columns)->whereMyPk()->select();
    return $model === null ? null : $this->mergeArray($model->toArray());
  }
  public function getIds() : array {
    $ids = [];
    foreach ($this->getPks() as $pk) {
      $ids[$pk] = $this->___props[$pk] ?? null;
    }
    return $ids;
  }
  public function exists() : bool {
    foreach ($this->getPks() as $pk) {
      if (empty($this->___props[$pk])) { return false; }
    }
    return true;
  }
  public function getValue() : mixed {
    return $this->id;
  }
  public function getLabel() : mixed {
    return $this->name;
  }
}
