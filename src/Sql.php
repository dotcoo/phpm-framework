<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

// Sql sql构造
// #[AllowDynamicProperties]
class Sql {
  // 删除模式
  const MODE_DELETE = 1; // 物理删除
  const MODE_MARK = 2; // 标记删除
  const MODE_MARK_DELETE = 3; // 先标记删除,再物理删除

  // 全局数据库连接
  public static ?\PDO $conn = null;

  // 实体
  protected ?Model $_model = null;

  // 数据
  protected array $_record = [];

  // 连接对象
  protected ?\PDO $_conn = null;

  // 数据库名
  protected string $_database = '';

  // 表名
  protected string $_table = '';

  // 别名
  protected string $_alias = '';

  // 表名
  protected string $_from = '';

  // 主键名
  protected array $_pks = ['id'];

  // 主键字段是否自增
  protected bool $_autoIncrement = true;

  // 时间字段是否自动添加
  protected bool $_autoTimeColumn = false;

  // 删除模式
  protected int $_deleteMode = 1;

  // sql 关键字
  protected array $_keywords = [];

  // 查询的列
  protected array $_columns = [];

  // 连表查询
  protected array $_joins = [];

  // 查询条件
  protected array $_wheres = [];

  // 查询条件的参数
  protected array $_wheresArgs = [];

  // 分组
  protected array $_groups = [];

  // 分组过滤
  protected array $_havings = [];

  // 分组过滤的参数
  protected array $_havingsArgs = [];

  // 排序
  protected array $_orders = [];

  // 限制行数
  protected int $_limit = -1;

  // 跳过行数
  protected int $_offset = -1;

  // 排他锁
  protected string $_forUpdate = '';

  // 共享锁
  protected string $_lockInShareMode = '';

  // 插入的列
  protected array $_cols = [];

  // 插入的值
  protected array $_colsArgs = [];

  // 更新的列
  protected array $_sets = [];

  // 更新的值
  protected array $_setsArgs = [];

  // sql语句
  protected string $_sql = '';

  // sql参数
  protected array $_args = [];

  // PDO结果集
  protected ?\PDOStatement $_stmt = null;

  // 结果集
  protected ?ArrayList $_rows = null;

  // 影响行数
  protected int $_affected = 0;

  // 自增ID
  protected int $_insertId = 0;

  // 设置实体
  public function model(Model $model) : static {
    $this->_model = $model;
    $this->_record = $model->toRecord();
    $this->_database = $model->getDatabase();
    $this->_table =  $model->getTable();
    $this->_alias =  $model->getAlias();
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    $this->_pks = $model->getPks();
    $this->_autoIncrement = $model->getAutoIncrement();
    $this->_autoTimeColumn = $model->getAutoTimeColumn();
    $this->_deleteMode = $model->getDeleteMode();
    return $this;
  }

  // 获取实体
  public function getModel() : Model {
    return $this->_model;
  }

  // 设置记录
  public function record(array $record) {
    $this->_record = $record; return $this;
  }

  // 获取记录
  public function getRecord() {
    return $this->_record;
  }

  // 设置数据库连接
  public function conn(\PDO $conn) : static {
    $this->_conn = $conn; return $this;
  }

  // 获取数据库连接
  public function getConn() {
    // TODO 后期调整
    return $this->_conn ?: $this->_model->getConn() ?: $this->_model::class::$conn ?: $this::class::$conn;
  }

  // 回收数据库连接
  public function putConn(\PDO $conn) : static {
    // TODO 暂未实现
    return $this;
  }

  // 设置数据库名
  public function database(string $database) : static {
    $this->_database = $database;
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    return $this;
  }

  // 获取数据库名
  public function getDatabase(): string {
    return $this->_database;
  }

  // 设置表名
  public function table(string $table) : static {
    $this->_table = $table;
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    return $this;
  }

  // 获取表名
  public function getTable() : string {
    return $this->_table;
  }

  // 设置表名
  public function alias(string $alias) : static {
    $this->_alias = $alias;
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    return $this;
  }

  // 获取表名
  public function getAlias() : string {
    return $this->_alias;
  }

  // 获取表名
  protected function databaseTableAlias(string $database, string $table, string $alias) : string {
    $database = $database ? "`{$database}`." : '';
    $table = "`{$table}`";
    $alias = $alias ? " AS `{$alias}`" : '';
    return "$database$table$alias";
  }

  // 设置主键名
  public function pks(string ...$pks) : static {
    $this->_pks = $pks; return $this;
  }

  // 获取主键名
  public function getPks() : array {
    return $this->_pks;
  }

  // 设置主键是否自增
  public function autoIncrement(bool $autoIncrement) : static {
    $this->_autoIncrement = $autoIncrement; return $this;
  }

  // 获取主键是否自增
  public function getAutoIncrement() : bool {
    return $this->_autoIncrement;
  }

  // 设置是否有时间字段
  public function autoTimeColumn(bool $autoTimeColumn) : static {
    $this->_autoTimeColumn = $autoTimeColumn; return $this;
  }

  // 获取是否有时间字段
  public function getAutoTimeColumn() : bool {
    return $this->_autoTimeColumn;
  }

  // 设置删除模式
  public function deleteMode(int $deleteMode) : static {
    $this->_deleteMode = $deleteMode; return $this;
  }

  // 获取删除模式
  public function getDeleteMode() : int {
    return $this->_deleteMode;
  }

  // 设置 sql 关键字
  public function keywords(string ...$keywords) : static {
    array_push($this->_keywords, ...$keywords); return $this;
  }

  // 设置 IGNORE 关键字
  public function ignore() : static {
    return $this->keywords('IGNORE');
  }

  // 设置 DELAYED 关键字
  public function delayed() : static {
    return $this->keywords('DELAYED');
  }

  // 设置查询的列名
  public function columns(string ...$columns) : static {
    array_push($this->_columns, ...array_map(fn($column) => "`$column`", $columns)); return $this;
  }

  // 设置查询的表达式
  public function columnsExpr(string ...$columns) : static {
    array_push($this->_columns, ...$columns); return $this;
  }

  // 设置join
  public function join(string $table, string $alias, string $cond) : static {
    array_push($this->_joins, " LEFT JOIN " . $this->databaseTableAlias('', $table, $alias) . " ON " . $cond); return $this;
  }

  // 条件查询
  public function where(string $where, mixed ...$args) : static {
    $wheres = explode('?', $where);
    $wheresArgs = [];
    foreach ($args as $i => $arg) {
      if (is_array($arg)) {
        $arg = $arg ?: [-1];
        $wheres[$i] .= str_repeat('?, ', count($arg) - 1);
        array_push($wheresArgs, ...$arg);
      } else {
        array_push($wheresArgs, $arg);
      }
    }
    array_push($this->_wheres, implode('?', $wheres));
    array_push($this->_wheresArgs, ...$wheresArgs);
    return $this;
  }

  // 分组
  public function group(string ...$groups) : static {
    array_push($this->_groups, ...$groups); return $this;
  }

  // 过滤查询
  public function having(string $having, mixed ...$args) : static {
    $havings = explode('?', $having);
    $havingsArgs = [];
    foreach ($args as $i => $arg) {
      if (is_array($arg)) {
        $havings[$i] .= str_repeat('?, ', count($arg) - 1);
        array_push($havingsArgs, ...$arg);
      } else {
        array_push($havingsArgs, $arg);
      }
    }
    array_push($this->_havings, implode('?', $havings));
    array_push($this->_havingsArgs, ...$havingsArgs);
    return $this;
  }

  // 排序
  public function order(string ...$orders) : static {
    array_push($this->_orders, ...$orders); return $this;
  }

  // 限制返回的行数
  public function limit(int $limit) : static {
    $this->_limit = $limit; return $this;
  }

  // 跳过的行数
  public function offset(int $offset) : static {
    $this->_offset = $offset; return $this;
  }

  // 排他锁
  public function forUpdate() : static {
    $this->_forUpdate = ' FOR UPDATE'; return $this;
  }

  // 共享锁
  public function lockInShareMode() : static {
    $this->_lockInShareMode = ' LOCK IN SHARE MODE'; return $this;
  }

  // 插入列
  public function col(string $col, mixed $arg) : static {
    $this->_model[$col] = $arg;
    $this->_record[$col] = $arg;
    array_push($this->_cols, "`$col`");
    array_push($this->_colsArgs, $arg);
    return $this;
  }

  // 设置插入列
  public function cols(string ...$cols) : static {
    foreach ($cols as $col) {
      array_push($this->_cols, "`$col`");
      array_push($this->_colsArgs, $this->_record[$col]);
    }
    return $this;
  }

  // 更新列
  public function set(string $set, mixed $arg) : static {
    $this->_model[$set] = $arg;
    $this->_record[$set] = $arg;
    array_push($this->_sets, "`$set` = ?");
    array_push($this->_setsArgs, $arg);
    return $this;
  }

  // 设置修改列
  public function sets(string ...$sets) : static {
    foreach ($sets as $set) {
      array_push($this->_sets, "`$set` = ?");
      array_push($this->_setsArgs, $this->_record[$set]);
    }
    return $this;
  }

  // 更新列
  public function setExpr(string $set, mixed ...$Args) : static {
    array_push($this->_sets, $set);
    array_push($this->_setsArgs, ...$Args);
    return $this;
  }

  // 获取sql语句
  public function sql() : string {
    return $this->_sql;
  }

  // 获取sql参数
  public function args() : array {
    return $this->_args;
  }

  // 获取 PDOStatement
  public function stms() : ?\PDOStatement {
    return $this->_stmt;
  }

  // 获取查询结果
  public function rows() : ?ArrayList {
    return $this->_rows;
  }

  // 获取影响行数
  public function affected() : ?int {
    return $this->_affected;
  }

  // 获取自增ID
  public function insertId() : ?int {
    return $this->_insertId;
  }

  // 根据主键, 查询一条数据
  public function whereByPk(mixed ...$ids) : static {
    $pks = $this->getPks();
    if (count($ids) !== count($pks)) { throw new \LogicException('$ids and $pks count are not equal!'); }
    foreach ($pks as $i => $pk) {
      $this->_record[$pk] = $ids[$i];
      $this->_model[$pk] = $ids[$i];
      $this->where("`{$pk}` = ?", $ids[$i]);
    }
    return $this;
  }

  // 根据列查询数据
  public function whereBy(string $column, mixed $value) : static {
    $this->_record[$column] = $value;
    $this->_model[$column] = $value;
    $this->where("`$column` = ?", $value); return $this;
  }

  // 根据主键查询
  public function whereMyPk() : static {
    foreach ($this->getPks() as $pk) {
      $this->where("`{$pk}` = ?", $this->_record[$pk]);
    }
    return $this;
  }

  // 根据属性查询
  public function whereMy(string ...$props) : static {
    foreach ($props as $prop) {
      $this->where("`$prop` = ?", $this->_record[$prop]);
    }
    return $this;
  }

  // 分页
  public function page(int $page = 1, int $pagesize = 15) : static {
    $this->limit($pagesize);
    $this->offset(($page - 1) * $pagesize);
    return $this;
  }

  // 增加
  public function plus(string $col, int $num = 1) : static {
    return $this->setExpr("`{$col}` = `{$col}` + ?", $num);
  }

  // 自增
  public function incr(string $col, int $num = 1) : static {
    return $this->setExpr("`{$col}` = last_insert_id(`{$col}` + ?)", $num);
  }

  // 生成 SELECT 语句和参数
  public function toSelect() : array {
    $from      = $this->_from;
    $keywords  = empty($this->_keywords)     ? ''  : ' ' . implode(' ', $this->_keywords);
    $columns   = empty($this->_columns)      ? '*' : implode(', ', $this->_columns);
    $joins     = empty($this->_joins)        ? ''  : implode(' ', $this->_joins);
    $wheres    = empty($this->_wheres)       ? ''  : ' WHERE ' . implode(' AND ', $this->_wheres);
    $groups    = empty($this->_groups)       ? ''  : ' GROUP BY ' . implode(', ', $this->_groups);
    $havings   = empty($this->_havings)      ? ''  : ' HAVING ' . implode(' AND ', $this->_havings);
    $orders    = empty($this->_orders)       ? ''  : ' ORDER BY ' . implode(', ', $this->_orders);
    $limit     = $this->_limit        === -1 ? ''  : ' LIMIT ?';
    $offset    = $this->_offset       === -1 ? ''  : ' OFFSET ?';
    $forUpdate = $this->_forUpdate;
    $lockInShareMode = $this->_lockInShareMode;
    $this->_sql = "SELECT{$keywords} {$columns} FROM {$from}{$joins}{$wheres}{$groups}{$havings}{$orders}{$limit}{$offset}{$forUpdate}{$lockInShareMode}";
    $this->_args = [...$this->_wheresArgs, ...$this->_havingsArgs];
    if ($this->_limit !== -1) {
      array_push($this->_args, $this->_limit);
    }
    if ($this->_offset !== -1) {
      array_push($this->_args, $this->_offset);
    }
    return [$this->_sql, ...$this->_args];
  }

  // 生成 INSERT 语句和参数
  public function toInsert() : array {
    if (empty($this->_cols)) { throw new \LogicException('$this->_cols is empty!'); }
    if (empty($this->_colsArgs)) { throw new \LogicException('$this->_colsArgs is empty!'); }
    $from     = $this->_from;
    $keywords = empty($this->_keywords)     ? ''  : ' ' . implode(' ', $this->_keywords);
    $cols     = implode(', ', $this->_cols);
    $placeholder = '?' . str_repeat(', ?', count($this->_colsArgs) - 1);
    $onDuplicateKeyUpdate = empty($this->_sets) ? '' : ' ON DUPLICATE KEY UPDATE ' . implode(', ', $this->_sets);
    $this->_sql = "INSERT{$keywords} INTO {$from} ({$cols}) VALUES ({$placeholder}){$onDuplicateKeyUpdate}";
    $this->_args = [...$this->_colsArgs, ...$this->_setsArgs];
    return [$this->_sql, ...$this->_args];;
  }

  // 生成 UPDATE 语句和参数
  public function toUpdate() : array {
    if (empty($this->_sets)) { throw new \LogicException('$this->_sets is empty!'); }
    if (empty($this->_setsArgs)) { throw new \LogicException('$this->_setsArgs is empty!'); }
    if (empty($this->_wheres)) {
      throw new \LogicException('$this->_wheres is empty!');
    }
    $from     = $this->_from;
    $sets     = implode(', ', $this->_sets);
    $wheres   = empty($this->_wheres)       ? '' : ' WHERE ' . implode(' AND ', $this->_wheres);
    $orders   = empty($this->_orders)       ? '' : ' ORDER BY ' . implode(', ', $this->_orders);
    $limit    = $this->_limit  === -1       ? '' : ' LIMIT ?';
    $this->_sql = "UPDATE {$from} SET {$sets}{$wheres}{$orders}{$limit}";
    $this->_args = [...$this->_setsArgs, ...$this->_wheresArgs];
    if ($this->_limit !== -1) {
      array_push($this->_args, $this->_limit);
    }
    return [$this->_sql, ...$this->_args];;
  }

  // 生成 REPLACE 语句和参数
  public function toReplace() : array {
    if (empty($this->_sets)) { throw new \LogicException('$this->_sets is empty!'); }
    if (empty($this->_setsArgs)) { throw new \LogicException('$this->_setsArgs is empty!'); }
    $from     = $this->_from;
    $sets     = implode(', ', $this->_sets);
    $this->_sql = "REPLACE INTO {$from} SET {$sets}";
    $this->_args = [...$this->_setsArgs];
    return [$this->_sql, ...$this->_args];;
  }

  // 生成 DELETE 语句和参数
  public function toDelete() : array {
    if (empty($this->_wheres)) { throw new \LogicException('$this->_wheres is empty!'); }
    $from     = $this->_from;
    $wheres   = empty($this->_wheres)       ? '' : ' WHERE ' . join(' AND ', $this->_wheres);
    $orders   = empty($this->_orders)       ? '' : ' ORDER BY ' . join(', ', $this->_orders);
    $limit    = $this->_limit  === -1       ? '' : ' LIMIT ?';
    $this->_sql = "DELETE FROM {$from}{$wheres}{$orders}{$limit}";
    $this->_args = [...$this->_wheresArgs];
    if ($this->_limit !== -1) {
      array_push($this->_args, $this->_limit);
    }
    return [$this->_sql, ...$this->_args];;
  }

  // 构建sql
  public function build($mode = 'select') : static {
    switch ($mode) {
      case 'select': $this->toSelect(); break;
      case 'insert': $this->toInsert(); break;
      case 'update': $this->toUpdate(); break;
      case 'delete': $this->toDelete(); break;
      default: throw new \LogicException('sql mode error');
    }
    return $this;
  }

  // 执行sql
  public function execute() : static {
    if ($this->_sql == '') { throw new \LogicException('sql is mepty'); }
    if (str_contains($this->_sql, "'") || str_contains($this->_sql, '"')) { throw new \LogicException("a ha ha ha ha ha ha ha ha!"); }
    log_debug($this->_sql, ...$this->_args);
    $conn = $this->getConn();
    $stmt = $this->_stmt = $conn->prepare($this->_sql);
    $stmt->execute($this->_args);
    $this->_rows = ArrayList::new($stmt->fetchAll());
    $this->_affected = $stmt->rowCount();
    $this->_insertId = intval($conn->lastInsertId());
    // $this->_insertId = intval($stmt->lastInsertId()); // \PDOStatement::lastInsertId is hack
    $this->putConn($conn);
    return $this;
  }

  // 执行查询并返回多条结果
  public function selectAll($deletedTime = true) : ArrayList {
    if ($deletedTime && ($this->_deleteMode & self::MODE_MARK) === self::MODE_MARK) {
      $this->where('`deletedTime` IS NULL');
    }
    return $this->build('select')->execute()->_rows->map(fn($v) => (new ($this->_model::class))->mergeRecord($v));
  }

  // 执行查询并返回一条结果
  public function select($deletedTime = true) : mixed {
    return $this->limit(1)->selectAll($deletedTime)->first();
  }

  // 执行插入并返回结果, insertId 是自增 id 的值
  public function insert() : static {
    if ($this->_autoTimeColumn) {
      $this->col('createdTime', time())->col('updatedTime', time());
    }
    $this->build('insert')->execute();
    $pk = $this->getPks()[0];
    if ($this->_autoIncrement && !in_array("`$pk`", $this->cols)) {
      $this->_model[$pk] = $this->_insertId;
      $this->_record[$pk] = $this->_insertId;
    }
    return $this;
  }

  // 执行更新并返回结果, affected 是影响的行数
  public function update($deletedTime = true) : static {
    if ($deletedTime && ($this->_deleteMode & self::MODE_MARK) === self::MODE_MARK) {
      this.where('`deletedTime` IS NULL');
    }
    if ($this->_autoTimeColumn) {
      $this->col('updatedTime', time());
    }
    return $this->build('update')->execute();
  }

  // // 执行替换并返回结果, insertId 是自增 id 的值, affected 是影响的行数
  // public function replace() : static {
  //   return $this->query(...$this->toReplace());
  // }

  // 执行删除并返回结果, affected 是影响的行数
  public function delete($deletedTime = true) : static {
    if ($deletedTime && ($this->_deleteMode & self::MODE_MARK) === self::MODE_MARK) {
      $this->where('`deletedTime` IS NULL');
    }
    if (($this->_deleteMode & self::MODE_MARK) === self::MODE_MARK) {
      $this->set('deletedTime', time())->build('update')->execute();
    }
    if (($this->_deleteMode & self::MODE_DELETE) === self::MODE_DELETE) {
      $this->build('delete')->execute();
    }
    return $this;
  }

  // 克隆
  public function clone() : Sql {
    $sql = new static();
    $sql->_model = $this->_model->clone();
    $sql->_record = $sql->_model->toRecord();
    $sql->_conn = $this->_conn;
    $sql->_database = $this->_database;
    $sql->_table = $this->_table;
    $sql->_alias = $this->_alias;
    $sql->_from = $this->_from;
    $sql->_pks = [...$this->_pks];
    $sql->_autoIncrement = $this->_autoIncrement;
    $sql->_autoTimeColumn = $this->_autoTimeColumn;
    $sql->_deleteMode = $this->_deleteMode;
    $sql->_keywords = [...$this->_keywords];
    $sql->_columns = [...$this->_columns];
    $sql->_joins = [...$this->_joins];
    $sql->_wheres = [...$this->_wheres];
    $sql->_wheresArgs = [...$this->_wheresArgs];
    $sql->_groups = [...$this->_groups];
    $sql->_havings = [...$this->_havings];
    $sql->_havingsArgs = [...$this->_havingsArgs];
    $sql->_orders = [...$this->_orders];
    $sql->_limit = $this->_limit;
    $sql->_offset = $this->_offset;
    $sql->_forUpdate = $this->_forUpdate;
    $sql->_lockInShareMode = $this->_lockInShareMode;
    $sql->_cols = [...$this->_cols];
    $sql->_colsArgs = [...$this->_colsArgs];
    $sql->_sets = [...$this->_sets];
    $sql->_setsArgs = [...$this->_setsArgs];
    return $sql;
  }

  // 统计Sql
  protected function countSql() : Sql {
    $sql = $this->clone();
    $sql->_columns = [];
    $sql->_groups = [];
    $sql->_havings = [];
    $sql->_havingsArgs = [];
    $sql->_orders = [];
    $sql->_limit = -1;
    $sql->_offset = -1;
    $sql->_forUpdate = '';
    $sql->_lockInShareMode = '';
    $sql->_cols = [];
    $sql->_colsArgs = [];
    $sql->_sets = [];
    $sql->_setsArgs = [];
    return $sql;
  }

  // 统计总行数
  public function count() : int {
    return $this->countSql()->columnsExpr('count(*) AS `count`')->build('select')->execute()->_rows[0]['count'];
  }

  // 根据指定字段查询
  protected function callWhereBy(string $name, mixed ...$args) {
    return $this->where("`$name` = ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereEq(string $name, mixed ...$args) {
    return $this->where("`$name` = ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereLt(string $name, mixed ...$args) {
    return $this->where("`$name` < ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereGt(string $name, mixed ...$args) {
    return $this->where("`$name` > ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereLe(string $name, mixed ...$args) {
    return $this->where("`$name` <= ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereGe(string $name, mixed ...$args) {
    return $this->where("`$name` >= ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereNe(string $name, mixed ...$args) {
    return $this->where("`$name` <> ?", ...$args);
  }

  // 根据指定字段查询
  protected function callWhereIn(string $name, mixed ...$args) {
    return $this->where("`$name` IN (?)", $args[0]);
  }

  // 根据指定字段查询
  protected function callWhereLike(string $name, mixed ...$args) {
    return $this->where("`$name` LIKE ?", "%{$args[0]}%");
  }

  // 根据指定字段查询
  protected function callWhereBetween(string $name, mixed ...$args) {
    return $this->where("`$name` BETWEEN ? AND ?", $args[0], $args[0]);
  }

  // 动态调用
  public function __call(string $name, array $args) : mixed {
    if (str_starts_with($name, 'whereBy') && $name !== 'whereBy') {
      return $this->callWhereBy(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereLt') && $name !== 'whereLt') {
      return $this->callWhereLt(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereGt') && $name !== 'whereGt') {
      return $this->callWhereGt(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereEq') && $name !== 'whereEq') {
      return $this->callWhereEq(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereLe') && $name !== 'whereLe') {
      return $this->callWhereLe(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereGe') && $name !== 'whereGe') {
      return $this->callWhereGe(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereNe') && $name !== 'whereNe') {
      return $this->callWhereNe(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereIn') && $name !== 'whereIn') {
      return $this->callWhereIn(lcfirst(substr($name, 7)), ...$args);
    } elseif (str_starts_with($name, 'whereLike') && $name !== 'whereLike') {
      return $this->callWhereLike(lcfirst(substr($name, 9)), ...$args);
    } elseif (str_starts_with($name, 'whereBetween') && $name !== 'whereBetween') {
      return $this->callWhereBetween(lcfirst(substr($name, 12)), ...$args);
    }
    throw new \BadMethodCallException("method Sql::$name not found!");
  }
}
