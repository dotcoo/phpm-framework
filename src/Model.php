<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable, PDO, BadMethodCallException;

use zay\interfaces\EventInterface;
use \zay\interfaces\StateInterface;
use zay\traits\DynamicTrait;
use zay\traits\EventTrait;

abstract class Model implements ArrayAccess, Countable, IteratorAggregate, Serializable, JsonSerializable, EventInterface, StateInterface {
  // 删除模式
  const MODE_DELETE = 1; // 物理删除
  const MODE_MARK = 2; // 标记删除
  const MODE_MARK_DELETE = 3; // 先标记删除,再物理删除

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

  public function toArray() : array {
    return $this->___props;
  }

  public function mergeRecord(array $record) : static { // storage to memory, subclass overwrite
    foreach($record as $name => $value) {
      $this->___props[$name] = $value;
    }
    return $this;
  }

  public function toRecord() : array { // memory to storage, subclass overwrite
    $record = [];
    foreach($this->___props as $name => $value) {
      $record[$name] = $this[$name];
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

  public function ignoreChange(mixed $retval) : mixed {
    $this->___changes = [];
    return $retval;
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

  // 数据库连接
  public static ?PDO $conn = null;

  // 数据库名称
  public static ?string $database = null;

  // 表名
  public static ?string $table = null;

  // 别名
  public static ?string $alias = null;

  // 主键
  public static ?array $pks = null;

  // 是否主键自增
  public static ?bool $autoIncrement = null;

  // 是否有时间列
  public static ?bool $autoTimeColumn = null;

  // 删除模式
  public static ?int $deleteMode = null;

  // 数据库连接
  protected ?PDO $_conn = null;

  // 数据库名称
  protected ?string $_database = null;

  // 表名
  protected ?string $_table = null;

  // 别名
  protected ?string $_alias = null;

  // 主键
  protected ?array $_pks = null;

  // 是否主键自增
  protected ?bool $_autoIncrement = null;

  // 是否有时间列
  protected ?bool $_autoTimeColumn = null;

  // 删除模式
  protected ?int $_deleteMode = null;

  // 设置当前实体对象的数据库连接对象
  public function setConn(?PDO $conn) : static {
    $this->_conn = $conn; return $this;
  }

  // 获取当前实体对象的数据库连接对象
  public function getConn() : ?PDO {
    // TODO 需要连接池
    return $this->_conn ?? $this::class::$conn ?? null; // ?: MySQLPool->getInstance()->get();
  }

  // 设置当前实体对象的数据库名
  public function setDatabase(?string $database) : static {
    return $this->_database = $database; return $this;
  }

  // 获取当前实体对象的数据库名
  public function getDatabase() : string {
    return $this->_database ?? $this::class::$database ?? '';
  }

  // 设置当前实体对象的表名
  public function setTable(?string $table) : static {
    $this->_table = $table; return $this;
  }

  // 获取当前实体对象的表名
  public function getTable() : string {
    return env('APP_DB_PREFIX') . ($this->_table ?? $this::class::$table ?? camel2under(pascal2camel(substr(static::class, strrpos(static::class, '\\') + 1))));
  }

  // 设置当前实体对象的别名
  public function setAlias(?string $alias) : static {
    $this->_alias = $alias; return $this;
  }

  // 获取返回当前实体对象的别名
  public function getAlias() : string {
    return $this->_alias ?? $this::class::$alias ?? '';
  }

  // 设置当前实体对象的主键名
  public function setPks(?array $pks) : static {
    $this->_pks = $pks; return $this;
  }

  // 获取当前实体对象的主键名
  public function getPks() : array {
    return $this->_pks ?? $this::class::$pks ?? ['id'];
  }

  // 设置是否为自增主键
  public function setAutoIncrement(?bool $autoIncrement) : static {
    $this->_autoIncrement = $autoIncrement; return $this;
  }

  // 获取是否为自增主键
  public function getAutoIncrement() : bool {
    return $this->_autoIncrement ?? $this::class::$autoIncrement ?? true;
  }

  // 设置是否有时间列
  public function setAutoTimeColumn(?bool $autoTimeColumn) : static {
    $this->_autoTimeColumn = $autoTimeColumn; return $this;
  }

  // 是否自动添加时间字段
  public function getAutoTimeColumn() : bool {
    return $this->_autoTimeColumn ?? $this::class::$autoTimeColumn ?? true;
  }

  // 设置删除模式
  public function setDeleteMode(string $deleteMode) : static {
    $this->_deleteMode = $deleteMode; return $this;
  }

  // 获取删除模式
  public function getDeleteMode() : int {
    return $this->_deleteMode ?? $this::class::$deleteMode ?? $this::class::MODE_MARK;
  }

  // 创建Sql
  public function newSql() : Sql {
    return (new Sql())->model($this);
  }

  // 添加数据
  public function add(string ...$columns) : Sql {
    $columns = !empty($columns) ? $columns : array_keys($this->___changes);
    return $this->ignoreChange($this->newSql()->cols(...$columns)->insert());
  }

  // 编辑数据
  public function edit(string ...$columns) : Sql {
    $columns = !empty($columns) ? $columns : array_keys($this->___changes);
    return $this->ignoreChange($this->newSql()->sets(...$columns)->whereMyPk()->update());
  }

  // 根据数据中的id, 删除数据
  public function del() : Sql {
    return $this->ignoreChange($this->newSql()->whereMyPk()->delete());
  }

  // 保存数据
  public function save(string ...$columns) : Sql {
    return $this->exists() ? $this->edit(...$columns) : $this->add(...$columns);
  }

  // 加载数据
  public function load(string ...$columns) : ?static {
    $model = $this->newSql()->columns(...$columns)->whereMyPk()->select();
    return $model === null ? null : $this->mergeArray($model->toArray());
  }

  // 是否存在
  public function exists() : bool {
    foreach ($this->getPks() as $pk) {
      if (empty($this->___props[$pk])) { return false; }
    }
    return true;
  }

  // 值
  public function getValue() : mixed {
    return $this->id;
  }

  // 标签
  public function getLabel() : mixed {
    return $this->name;
  }

  // 静态查找
  public static function find(mixed ...$pks) : ?static {
    return static::new()->newSql()->whereByPk(...$pks)->select();
  }

  // 动态调用
  public function __call(string $name, array $args) : mixed {
    $err = null;
    try { return $this->___call($name, $args); } catch (BadMethodCallException $e) { $err = $e; }
    try { return $this->newSql()->$name(...$args); } catch (BadMethodCallException $e) { $err = $e; }
    throw $err;
  }
}
