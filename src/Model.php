<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use zay\interfaces\EventCenterInterface;
use zay\interfaces\StateInterface;
use zay\traits\Dynamic;
use zay\traits\EventCenter;

abstract class Model implements \ArrayAccess, \Countable, \IteratorAggregate, \Serializable, \JsonSerializable, EventCenterInterface, StateInterface {

  use Dynamic;
  use EventCenter;

  // 删除模式
  const MODE_DELETE = 1; // 物理删除
  const MODE_MARK = 2; // 标记删除
  const MODE_MARK_DELETE = 3; // 先标记删除,再物理删除

  // 创建对象
  public static function new(array|Model $array = [], bool $ignoreChange = false) : static {
    return (new static())->mergeArrayAlias($array, $ignoreChange);
  }

  // 合并数组
  public function mergeArrayAlias(array|Model $array, bool $ignoreChange = false) : static {
    foreach($array as $name => $value) {
      $this->___props[$name] = $value;
      if ($ignoreChange) { continue; }
      $this->___changes[$name] = true;
    }
    return $this;
  }

  // 转换为数组
  public function toArray() : array {
    return $this->___props;
  }

  // 合并记录, 需要的时候请重写此方法
  public function mergeRecordAlias(array $record) : static { // storage to memory, subclass overwrite
    foreach($record as $name => $value) {
      $this->___props[$name] = $value;
    }
    return $this;
  }

  // 转换为记录, 需要的时候请重写此方法
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

  public function ignoreChange(bool $ignoreChange = true) : mixed {
    $this->___changes = $ignoreChange ? [] : $this->___changes;
    return $this;
  }

  public function __getState() : array {
    return ['___props' => $this->___props];
  }

  public static function __setState(array $data) : static {
    return static::new($data['___props']);
  }

  // 数据库连接
  public ?\PDO $_conn = null;

  // 数据库名称
  public string $_database = '';

  // 表名
  public string $_table = '';

  // 别名
  public string $_alias = '';

  // 主键
  public array $_pks = ['id'];

  // 是否主键自增
  public bool $_autoIncrement = true;

  // 是否有时间列
  public bool $_autoTimeColumn = true;

  // 删除模式
  public int $_deleteMode = 2;

  // 设置当前实体对象的数据库连接对象
  public function setConn(?\PDO $conn) : static {
    $this->_conn = $conn;
    return $this;
  }

  // 获取当前实体对象的数据库连接对象
  public function getConn() : \PDO {
    return $this->_conn ?? MySQLPool::getInstance()->get();
  }

  // 设置当前实体对象的数据库名
  public function setConn(string $database) : static {
    $this->_database = $database;
    return $this;
  }

  // 获取当前实体对象的数据库名
  public function getDatabase() : string {
    return $this->_database;
  }

  // 设置当前实体对象的表名
  public function setTable(string $table) : static {
    $this->_table = $table;
    return $this;
  }

  // 获取当前实体对象的表名
  public function getTable() : string {
    return APP_DATABASE_PREFIX . ($this->_table ?: camel2under(pascal2camel(class2class(static::class))));
  }

  // 设置当前实体对象的别名
  public function setAlias(string $alias) : static {
    $this->_alias = $alias;
    return $this;
  }

  // 获取当前实体对象的别名
  public function getAlias() : string {
    return $this->_alias;
  }

  // 设置当前实体对象的主键名
  public function setPks(string $pks) : static {
    $this->_pks = $pks;
    return $this;
  }

  // 获取当前实体对象的主键名
  public function getPks() : array {
    return $this->_pks;
  }

  // 设置是否为自增主键
  public function setAutoIncrement(bool $autoIncrement) : static {
    $this->_autoIncrement = $autoIncrement;
    return $this;
  }

  // 获取是否为自增主键
  public function getAutoIncrement() : bool {
    return $this->_autoIncrement;
  }

  // 设置是否有时间列
  public function setAutoTimeColumn(bool $autoTimeColumn) : static {
    $this->_autoTimeColumn = $autoTimeColumn;
    return $this;
  }

  // 是否自动添加时间字段
  public function getAutoTimeColumn() : bool {
    return $this->_autoTimeColumn;
  }

  // 设置删除模式
  public function setDeleteMode(int $deleteMode) : static {
    $this->_deleteMode = $deleteMode;
    return $this;
  }

  // 获取删除模式
  public function getDeleteMode() : int {
    return $this->_deleteMode;
  }

  // 是否开启缓存
  protected static bool $___cache = false;

  // 创建Sql
  public function newSqlAlias(?bool $cache = null) : Sql {
    if ($cache === true) {
      return (new SqlCache())->model($this);
    } elseif ($cache === false) {
      return (new Sql())->model($this);
    } elseif (static::$___cache === true) {
      return (new SqlCache())->model($this);
    } elseif (static::$___cache === false) {
      return (new Sql())->model($this);
    }
    throw new \LogicException('unreachable');
  }

  // 添加数据
  public function add(string ...$columns) : \PDOStatement {
    return $this->___add(true, ...$columns);
  }

  // 添加数据
  public function addNotAutoIncrement(string ...$columns) : \PDOStatement {
    return $this->___add(false, ...$columns);
  }

  // 添加数据
  protected function ___add(bool $autoIncrement, string ...$columns) : \PDOStatement {
    // $this->dispatchEvent('beforeInsert');
    $columns = !empty($columns) ? $columns : array_keys($this->___changes);
    $retval = $this->newSqlAlias()->cols(...$columns)->insert($autoIncrement);
    $this->ignoreChange();
    // $this->dispatchEvent('afterInsert');
    return $retval;
  }

  // 编辑数据
  public function edit(string ...$columns) : \PDOStatement {
    return $this->___edit(false, ...$columns);
  }

  // 编辑数据
  public function editIncludePk(string ...$columns) : \PDOStatement {
    return $this->___edit(true, ...$columns);
  }

  // 编辑数据
  protected function ___edit(bool $includePk, string ...$columns) : \PDOStatement {
    // $this->dispatchEvent('beforeUpdate');
    $columns = !empty($columns) ? $columns : array_keys($this->___changes);
    if (!$includePk) { $columns = array_diff($columns, $this->getPks()); }
    $retval = $this->newSqlAlias()->sets(...$columns)->whereMyPk()->update();
    $this->ignoreChange();
    // $this->dispatchEvent('afterUpdate');
    return $retval;
  }

  // 根据数据中的id, 删除数据
  public function del() : \PDOStatement {
    // $this->dispatchEvent('beforeDelete');
    $retval = $this->newSqlAlias()->whereMyPk()->delete();
    $this->ignoreChange();
    // $this->dispatchEvent('afterDelete');
    return $retval;
  }

  // 保存数据
  public function save(string ...$columns) : \PDOStatement {
    // $this->dispatchEvent('beforeSave');
    $retval = $this->exists() ? $this->edit(...$columns) : $this->add(...$columns);
    // $this->dispatchEvent('afterSave');
    return $retval;
  }

  // 加载最新的数据
  public function load(string ...$columns) : static {
    return $this->mergeArrayAlias($this->newSqlAlias()->columns(...$columns)->whereMyPk()->select());
  }

  // // 设置当前实体对象的主键
  // public function setIds(mixed ...$ids) : static {
  //   foreach ($this->getPks() as $i => $pk) {
  //     $this->___props[$pk] = $ids[$i];
  //   }
  //   return $this;
  // }

  // // 返回当前实体对象的主键
  // public function getIds() : array {
  //   $ids = [];
  //   foreach ($this->getPks() as $pk) {
  //     $ids[] = $this->___props[$pk];
  //   }
  //   return $ids;
  // }

  // // 是否为空
  // public function empty() : bool {
  //   return empty($this->___props);
  // }

  // 是否存在
  public function exists() : bool {
    foreach ($this->getPks() as $pk) {
      if (empty($this->___props[$pk])) {
        return false;
      }
    }
    return true;
  }

  public function getValue() : mixed {
    return $this->___props['id'];
  }

  public function getLabel() : mixed {
    return $this->___props['name'];
  }

  // 静态查找
  public static function find(mixed ...$pks) : ?static {
    return static::new()->newSqlAlias()->whereByPk(...$pks)->select();
  }

  // 动态调用
  public function __call(string $name, array $args) : mixed {
    if (str_starts_with($name, 'findBy') && $name !== 'findBy') {
      return $this->callFindBy($name, ...$args);
    } else {
      $err = null;
      try { return $this->___call($name, $args); } catch (\BadMethodCallException $e) { $err = $e; }
      try { return $this->newSqlAlias()->$name(...$args); } catch (\BadMethodCallException $e) {}
      throw $err;
    }
  }

  // // 表数据更新事件
  // public function onTableUpdate() : void {}

  // // 模型插入前事件
  // public function onBeforeInsert() : void {}

  // // 模型插入后事件
  // public function onAfterInsert() : void {}

  // // 模型更新前事件
  // public function onBeforeUpdate() : void {}

  // // 模型更新后事件
  // public function onAfterUpdate() : void {}

  // // 模型删除前事件
  // public function onBeforeDelete() : void {}

  // // 模型删除后事件
  // public function onAfterDelete() : void {}

  // // 模型保存前事件
  // public function onBeforeSave() : void {}

  // // 模型保存后事件
  // public function onAfterSave() : void {}

  // 清除缓存数据
  public static function clearTableCache() : void {
    return statis::new()->newSqlAlias(true)->clearTableCache();
  }

  // 获取缓存数据
  public static function getTableCache() : ArrayList {
    return statis::new()->newSqlAlias(true)->getTableCache();
  }

  // 刷新缓存数据
  public static function refershTableCache() : ArrayList {
    return statis::new()->newSqlAlias(true)->refershTableCache();
  }
}
