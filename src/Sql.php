<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework;

use PDO, PDOStatement, LogicException, BadMethodCallException;
final class Sql {
  const MODE_DELETE = 1;
  const MODE_MARK = 2;
  const MODE_MARK_DELETE = 3;
  public static ?PDO $conn = null;
  protected ?Model $_model = null;
  protected array $_record = [];
  protected ?PDO $_conn = null;
  protected string $_database = '';
  protected string $_table = '';
  protected string $_alias = '';
  protected string $_from = '';
  protected array $_pks = ['id'];
  protected bool $_autoIncrement = true;
  protected bool $_autoTimeColumn = false;
  protected int $_deleteMode = 1;
  protected array $_keywords = [];
  protected array $_columns = [];
  protected array $_joins = [];
  protected array $_wheres = [];
  protected array $_groups = [];
  protected array $_havings = [];
  protected array $_orders = [];
  protected int $_limit = -1;
  protected int $_offset = -1;
  protected string $_forUpdate = '';
  protected string $_lockInShareMode = '';
  protected array $_cols = [];
  protected array $_colsArgs = [];
  protected array $_sets = [];
  protected array $_setsArgs = [];
  protected string $_sql = '';
  protected array $_args = [];
  protected ?PDOStatement $_stmt = null;
  protected ?ArrayList $_rows = null;
  protected int $_affected = 0;
  protected int $_insertId = 0;
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
  public function getModel() : Model {
    return $this->_model;
  }
  public function record(array $record) {
    $this->_record = $record; return $this;
  }
  public function getRecord() {
    return $this->_record;
  }
  public function conn(PDO $conn) : static {
    $this->_conn = $conn; return $this;
  }
  public function getConn() {
    return $this->_conn ?: $this->_model->getConn() ?: $this->_model::class::$conn ?: $this::class::$conn;
  }
  public function putConn(PDO $conn) : static {
    return $this;
  }
  public function database(string $database) : static {
    $this->_database = $database;
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    return $this;
  }
  public function getDatabase(): string {
    return $this->_database;
  }
  public function table(string $table) : static {
    $this->_table = $table;
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    return $this;
  }
  public function getTable() : string {
    return $this->_table;
  }
  public function alias(string $alias) : static {
    $this->_alias = $alias;
    $this->_from = $this->databaseTableAlias($this->_database, $this->_table, $this->_alias);
    return $this;
  }
  public function getAlias() : string {
    return $this->_alias;
  }
  protected function databaseTableAlias(string $database, string $table, string $alias) : string {
    $database = $database ? "`{$database}`." : '';
    $table = "`{$table}`";
    $alias = $alias ? " AS `{$alias}`" : '';
    return "$database$table$alias";
  }
  public function pks(string ...$pks) : static {
    $this->_pks = $pks; return $this;
  }
  public function getPks() : array {
    return $this->_pks;
  }
  public function autoIncrement(bool $autoIncrement) : static {
    $this->_autoIncrement = $autoIncrement; return $this;
  }
  public function getAutoIncrement() : bool {
    return $this->_autoIncrement;
  }
  public function autoTimeColumn(bool $autoTimeColumn) : static {
    $this->_autoTimeColumn = $autoTimeColumn; return $this;
  }
  public function getAutoTimeColumn() : bool {
    return $this->_autoTimeColumn;
  }
  public function deleteMode(int $deleteMode) : static {
    $this->_deleteMode = $deleteMode; return $this;
  }
  public function getDeleteMode() : int {
    return $this->_deleteMode;
  }
  public function keywords(string ...$keywords) : static {
    array_push($this->_keywords, ...$keywords); return $this;
  }
  public function ignore() : static {
    return $this->keywords('IGNORE');
  }
  public function delayed() : static {
    return $this->keywords('DELAYED');
  }
  public function columns(string ...$columns) : static {
    array_push($this->_columns, ...array_map(fn($column) => "`$column`", $columns)); return $this;
  }
  public function columnsExpr(string ...$columns) : static {
    array_push($this->_columns, ...$columns); return $this;
  }
  public function join(string $table, string $alias, string $cond) : static {
    array_push($this->_joins, " LEFT JOIN " . $this->databaseTableAlias('', $table, $alias) . " ON " . $cond); return $this;
  }
  public function where(string|Sql $where, int|float|string|array ...$args) : static {
    if (is_string($where)) {
      $w = [explode('?', $where)];
      foreach ($args as $i => $arg) {
        if (is_array($arg)) {
          $arg = $arg ?: [-1];
          $w[0][$i] .= str_repeat('?, ', count($arg) - 1);
          array_push($w, ...$arg);
        } else {
          array_push($w, $arg);
        }
      }
      $w[0] = implode('?', $w[0]);
      array_push($this->_wheres, $w);
    } else {
      array_push($this->_wheres, $where);
    }
    return $this;
  }
  public function whereAnd(string|array|self ...$wheres) : static {
    $sql = new static();
    foreach ($wheres as $where) {
      if (is_array($where)) {
        $sql->where(...$where);
      } else {
        $sql->where($where);
      }
    }
    return $this->where(...$sql->toWhere(true, true));
  }
  public function whereOr(string|array|self ...$wheres) : static {
    $sql = new static();
    foreach ($wheres as $where) {
      if (is_array($where)) {
        $sql->where(...$where);
      } else {
        $sql->where($where);
      }
    }
    return $this->where(...$sql->toWhere(false, true));
  }
  public function group(string ...$groups) : static {
    array_push($this->_groups, ...$groups); return $this;
  }
  public function having(string|Sql $having, int|float|string|array ...$args) : static {
    if (is_string($having)) {
      $h = [explode('?', $having)];
      foreach ($args as $i => $arg) {
        if (is_array($arg)) {
          $h[0][$i] .= str_repeat('?, ', count($arg) - 1);
          array_push($h, ...$arg);
        } else {
          array_push($h, $arg);
        }
      }
      $h[0] = implode('?', $h[0]);
      array_push($this->_havings, $h);
    } else {
      array_push($this->_havings, $having);
    }
    return $this;
  }
  public function havingAnd(string|array|self ...$havings) : static {
    $sql = new static();
    foreach ($havings as $having) {
      if (is_array($having)) {
        $sql->having(...$having);
      } else {
        $sql->having($having);
      }
    }
    return $this->having($sql);
  }
  public function havingOr(string|array|self ...$havings) : static {
    $sql = new static();
    $sql->havingOr();
    foreach ($havings as $having) {
      if (is_array($having)) {
        $sql->having(...$having);
      } else {
        $sql->having($having);
      }
    }
    return $this->having($sql);
  }
  public function order(string ...$orders) : static {
    array_push($this->_orders, ...$orders); return $this;
  }
  public function limit(int $limit) : static {
    $this->_limit = $limit; return $this;
  }
  public function offset(int $offset) : static {
    $this->_offset = $offset; return $this;
  }
  public function forUpdate() : static {
    $this->_forUpdate = ' FOR UPDATE'; return $this;
  }
  public function lockInShareMode() : static {
    $this->_lockInShareMode = ' LOCK IN SHARE MODE'; return $this;
  }
  public function col(string $col, int|float|string $arg) : static {
    if ($this->_model) { $this->_model[$col] = $arg; }
    if ($this->_record) { $this->_record[$col] = $arg; }
    array_push($this->_cols, "`$col`");
    array_push($this->_colsArgs, $arg);
    return $this;
  }
  public function cols(string ...$cols) : static {
    foreach ($cols as $col) {
      array_push($this->_cols, "`$col`");
      array_push($this->_colsArgs, $this->_record[$col]);
    }
    return $this;
  }
  public function set(string $set, int|float|string $arg) : static {
    if ($this->_model) { $this->_model[$set] = $arg; }
    if ($this->_record) { $this->_record[$set] = $arg; }
    array_push($this->_sets, "`$set` = ?");
    array_push($this->_setsArgs, $arg);
    return $this;
  }
  public function sets(string ...$sets) : static {
    foreach ($sets as $set) {
      array_push($this->_sets, "`$set` = ?");
      array_push($this->_setsArgs, $this->_record[$set]);
    }
    return $this;
  }
  public function setExpr(string $set, int|float|string ...$Args) : static {
    array_push($this->_sets, $set);
    array_push($this->_setsArgs, ...$Args);
    return $this;
  }
  public function sql() : string {
    return $this->_sql;
  }
  public function args() : array {
    return $this->_args;
  }
  public function stms() : ?PDOStatement {
    return $this->_stmt;
  }
  public function rows() : ?ArrayList {
    return $this->_rows;
  }
  public function affected() : ?int {
    return $this->_affected;
  }
  public function insertId() : ?int {
    return $this->_insertId;
  }
  public function toWhere(bool $and = true, bool $parentheses = true) : array {
    $wheres = [[]];
    foreach ($this->_wheres as $where) {
      if (!is_string($where[0])) {
        $where = $where[0]->toWhere();
      }
      array_push($wheres[0], $where[0]);
      array_push($wheres, ...array_slice($where, 1));
    }
    $wheres[0] = implode($and ? ' AND ' : ' OR ', $wheres[0]);
    $wheres[0] = $parentheses ? '(' . $wheres[0] . ')' : $wheres[0];
    return $wheres;
  }
  public function toHaving(bool $and = true, bool $parentheses = true) : array {
    $havings = [[]];
    foreach ($this->_havings as $having) {
      if (!is_string($having[0])) {
        $having = $having[0]->toHaving();
      }
      array_push($havings[0], $having[0]);
      array_push($havings, ...array_slice($having, 1));
    }
    $havings[0] = implode($and ? ' AND ' : ' OR ', $havings[0]);
    $havings[0] = $parentheses ? '(' . $havings[0] . ')' : $havings[0];
    return $havings;
  }
  public function toSelect() : array {
    [$wheres,  $wheresArgs]  = array_expand($this->toWhere(true, false), 0);
    [$havings, $havingsArgs] = array_expand($this->toHaving(true, false), 0);
    $from      = $this->_from;
    $keywords  = empty($this->_keywords)     ? ''  : ' ' . implode(' ', $this->_keywords);
    $columns   = empty($this->_columns)      ? '*' : implode(', ', $this->_columns);
    $joins     = empty($this->_joins)        ? ''  : implode(' ', $this->_joins);
    $wheres    = empty($wheres)              ? ''  : ' WHERE ' . $wheres;
    $groups    = empty($this->_groups)       ? ''  : ' GROUP BY ' . implode(', ', $this->_groups);
    $havings   = empty($this->_havings)      ? ''  : ' HAVING ' . $havings;
    $orders    = empty($this->_orders)       ? ''  : ' ORDER BY ' . implode(', ', $this->_orders);
    $limit     = $this->_limit        === -1 ? ''  : ' LIMIT ?';
    $offset    = $this->_offset       === -1 ? ''  : ' OFFSET ?';
    $forUpdate = $this->_forUpdate;
    $lockInShareMode = $this->_lockInShareMode;
    $this->_sql = "SELECT{$keywords} {$columns} FROM {$from}{$joins}{$wheres}{$groups}{$havings}{$orders}{$limit}{$offset}{$forUpdate}{$lockInShareMode}";
    $this->_args = [...$wheresArgs, ...$havingsArgs];
    if ($this->_limit !== -1) {
      array_push($this->_args, $this->_limit);
    }
    if ($this->_offset !== -1) {
      array_push($this->_args, $this->_offset);
    }
    return [$this->_sql, ...$this->_args];
  }
  public function toInsert() : array {
    if (empty($this->_cols)) { throw new LogicException('$this->_cols is empty!'); }
    if (empty($this->_colsArgs)) { throw new LogicException('$this->_colsArgs is empty!'); }
    $from     = $this->_from;
    $keywords = empty($this->_keywords) ? '' : ' ' . implode(' ', $this->_keywords);
    $cols     = implode(', ', $this->_cols);
    $placeholder = '?' . str_repeat(', ?', count($this->_colsArgs) - 1);
    $onDuplicateKeyUpdate = empty($this->_sets) ? '' : ' ON DUPLICATE KEY UPDATE ' . implode(', ', $this->_sets);
    $this->_sql = "INSERT{$keywords} INTO {$from} ({$cols}) VALUES ({$placeholder}){$onDuplicateKeyUpdate}";
    $this->_args = [...$this->_colsArgs, ...$this->_setsArgs];
    return [$this->_sql, ...$this->_args];;
  }
  public function toUpdate() : array {
    if (empty($this->_sets)) { throw new LogicException('$this->_sets is empty!'); }
    if (empty($this->_setsArgs)) { throw new LogicException('$this->_setsArgs is empty!'); }
    if (empty($this->_wheres)) { throw new LogicException('$this->_wheres is empty!'); }
    [$wheres,  $wheresArgs] = array_expand($this->toWhere(true, false), 0);
    $from   = $this->_from;
    $sets   = implode(', ', $this->_sets);
    $wheres = empty($this->_wheres) ? '' : ' WHERE ' . $wheres;
    $orders = empty($this->_orders) ? '' : ' ORDER BY ' . implode(', ', $this->_orders);
    $limit  = $this->_limit  === -1 ? '' : ' LIMIT ?';
    $this->_sql = "UPDATE {$from} SET {$sets}{$wheres}{$orders}{$limit}";
    $this->_args = [...$this->_setsArgs, ...$wheresArgs];
    if ($this->_limit !== -1) {
      array_push($this->_args, $this->_limit);
    }
    return [$this->_sql, ...$this->_args];;
  }
  public function toReplace() : array {
    if (empty($this->_sets)) { throw new LogicException('$this->_sets is empty!'); }
    if (empty($this->_setsArgs)) { throw new LogicException('$this->_setsArgs is empty!'); }
    $from = $this->_from;
    $sets = implode(', ', $this->_sets);
    $this->_sql = "REPLACE INTO {$from} SET {$sets}";
    $this->_args = [...$this->_setsArgs];
    return [$this->_sql, ...$this->_args];;
  }
  public function toDelete() : array {
    if (empty($this->_wheres)) { throw new LogicException('$this->_wheres is empty!'); }
    [$wheres,  $wheresArgs] = array_expand($this->toWhere(true, false), 0);
    $from   = $this->_from;
    $wheres = empty($this->_wheres) ? '' : ' WHERE ' . $wheres;
    $orders = empty($this->_orders) ? '' : ' ORDER BY ' . implode(', ', $this->_orders);
    $limit  = $this->_limit  === -1 ? '' : ' LIMIT ?';
    $this->_sql = "DELETE FROM {$from}{$wheres}{$orders}{$limit}";
    $this->_args = [...$wheresArgs];
    if ($this->_limit !== -1) {
      array_push($this->_args, $this->_limit);
    }
    return [$this->_sql, ...$this->_args];;
  }
  public function build(string $mode = 'select') : static {
    switch ($mode) {
      case 'select': $this->toSelect(); break;
      case 'insert': $this->toInsert(); break;
      case 'update': $this->toUpdate(); break;
      case 'delete': $this->toDelete(); break;
      default: throw new LogicException('sql mode error');
    }
    return $this;
  }
  public function execute() : static {
    if ($this->_sql == '') { throw new LogicException('sql is mepty'); }
    if (str_contains($this->_sql, "'") || str_contains($this->_sql, '"')) { throw new LogicException("a ha ha ha ha ha ha ha ha!"); }
    log_debug($this->_sql, ...$this->_args);
    $conn = $this->getConn();
    $stmt = $this->_stmt = $conn->prepare($this->_sql);
    $stmt->execute($this->_args);
    $this->_rows = ArrayList::new($stmt->fetchAll());
    $this->_affected = $stmt->rowCount();
    $this->_insertId = intval($conn->lastInsertId());
    $this->putConn($conn);
    return $this;
  }
  public function selectAll(?bool $deletedTime = null) : ArrayList {
    if ($deletedTime !== null ? $deletedTime : ($this->_deleteMode & static::MODE_MARK) === static::MODE_MARK) {
      $this->where('`deletedTime` IS NULL');
    }
    return $this->build('select')->execute()->_rows->map(fn($v) => $this->_model::class::new()->mergeRecord($v));
  }
  public function select(?bool $deletedTime = null) : ?object {
    return $this->limit(1)->selectAll($deletedTime)->first();
  }
  public function insert() : static {
    if ($this->_autoTimeColumn) {
      $this->col('createdTime', time())->col('updatedTime', time());
    }
    $this->build('insert')->execute();
    $pk = $this->getPks()[0];
    if ($this->_autoIncrement && !in_array("`$pk`", $this->_cols)) {
      $this->_model[$pk] = $this->_insertId;
      $this->_record[$pk] = $this->_insertId;
    }
    return $this;
  }
  public function update(?bool $deletedTime = null) : static {
    if (empty($this->_wheres)) { throw new LogicException('$this->_wheres is empty!'); }
    if ($deletedTime !== null ? $deletedTime : ($this->_deleteMode & static::MODE_MARK) === static::MODE_MARK) {
      $this->where('`deletedTime` IS NULL');
    }
    if ($this->_autoTimeColumn) {
      $this->set('updatedTime', time());
    }
    return $this->build('update')->execute();
  }
  public function delete(?bool $deletedTime = null) : static {
    if (empty($this->_wheres)) { throw new LogicException('$this->_wheres is empty!'); }
    if ($deletedTime !== null ? $deletedTime : ($this->_deleteMode & static::MODE_MARK) === static::MODE_MARK) {
      $this->where('`deletedTime` IS NULL');
    }
    if (($this->_deleteMode & static::MODE_MARK) === static::MODE_MARK) {
      $this->set('deletedTime', time())->build('update')->execute();
    }
    if (($this->_deleteMode & static::MODE_DELETE) === static::MODE_DELETE) {
      $this->build('delete')->execute();
    }
    return $this;
  }
  public function whereByPk(int|float|string ...$ids) : static {
    $pks = $this->getPks();
    if (count($ids) !== count($pks)) { throw new LogicException('$ids and $pks count are not equal!'); }
    foreach ($pks as $i => $pk) {
      $this->_record[$pk] = $ids[$i];
      $this->_model[$pk] = $ids[$i];
      $this->where("`{$pk}` = ?", $ids[$i]);
    }
    return $this;
  }
  public function whereBy(string $column, int|float|string $value) : static {
    $this->_record[$column] = $value;
    $this->_model[$column] = $value;
    $this->where("`$column` = ?", $value);
    return $this;
  }
  public function whereByAssoc(array $data) : static {
    foreach ($data as $column => $value) {
      if ($this->_record) { $this->_record[$column] = $value; }
      if ($this->_model) { $this->_model[$column] = $value; }
      $this->where("`$column` = ?", $value);
    }
    return $this;
  }
  public function whereMyPk() : static {
    foreach ($this->getPks() as $pk) {
      $this->where("`{$pk}` = ?", $this->_record[$pk]);
    }
    return $this;
  }
  public function whereMy(string ...$props) : static {
    foreach ($props as $prop) {
      $this->where("`$prop` = ?", $this->_record[$prop]);
    }
    return $this;
  }
  public function page(int $page = 1, int $pagesize = 15) : static {
    $this->limit($pagesize);
    $this->offset(($page - 1) * $pagesize);
    return $this;
  }
  public function plus(string $col, int $num = 1) : static {
    return $this->setExpr("`{$col}` = `{$col}` + ?", $num);
  }
  public function incr(string $col, int $num = 1) : static {
    return $this->setExpr("`{$col}` = last_insert_id(`{$col}` + ?)", $num);
  }
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
    $sql->_groups = [...$this->_groups];
    $sql->_havings = [...$this->_havings];
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
  protected function countSql() : Sql {
    $sql = $this->clone();
    $sql->_columns = [];
    $sql->_groups = [];
    $sql->_havings = [];
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
  public function count() : int {
    return $this->countSql()->columnsExpr('count(*) AS `count`')->build('select')->execute()->_rows[0]['count'];
  }
  protected function callFindBy(string $name, mixed ...$args) {
    return $this->where("`$name` = ?", ...$args)->select();
  }
  protected function callWhereBy(string $name, mixed ...$args) {
    return $this->where("`$name` = ?", ...$args);
  }
  protected function callWhereEq(string $name, mixed ...$args) {
    return $this->where("`$name` = ?", ...$args);
  }
  protected function callWhereLt(string $name, mixed ...$args) {
    return $this->where("`$name` < ?", ...$args);
  }
  protected function callWhereGt(string $name, mixed ...$args) {
    return $this->where("`$name` > ?", ...$args);
  }
  protected function callWhereLe(string $name, mixed ...$args) {
    return $this->where("`$name` <= ?", ...$args);
  }
  protected function callWhereGe(string $name, mixed ...$args) {
    return $this->where("`$name` >= ?", ...$args);
  }
  protected function callWhereNe(string $name, mixed ...$args) {
    return $this->where("`$name` <> ?", ...$args);
  }
  protected function callWhereIn(string $name, mixed ...$args) {
    return $this->where("`$name` IN (?)", $args[0]);
  }
  protected function callWhereLike(string $name, mixed ...$args) {
    return $this->where("`$name` LIKE ?", "%{$args[0]}%");
  }
  protected function callWhereBetween(string $name, mixed ...$args) {
    return $this->where("`$name` BETWEEN ? AND ?", $args[0], $args[0]);
  }
  public function __call(string $name, array $args) : mixed {
    if (str_starts_with($name, 'findBy') && $name !== 'findBy') {
      return $this->callFindBy(lcfirst(substr($name, 6)), ...$args);
    } else if (str_starts_with($name, 'whereBy') && $name !== 'whereBy') {
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
    throw new BadMethodCallException("method Sql::$name not found!");
  }
}
