<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use PDOStatement, LogicException;

// Sql sql构造
class SqlCache extends Sql {
  public function cacheTableKey() : string {
    return "table.{$this->_database}.{$this->_table}";
  }

  public function clearTableCache() : static {
    $key = $this->cacheTableKey();
    data_del($key);
    return $this;
  }

  public function getTableCache() : ArrayList {
    $key = $this->cacheTableKey();
    return data_has($key) ? data_get($key) : $this->refershTableCache();
  }

  public function refershTableCache(mixed $retval = null) : mixed {
    $key = $this->cacheTableKey();
    $val = $this->getModel()->newSql(false)->selectList();
    data_set($key, $val);
    if ($this->_modelClass !== '') { $this->_model->dispatchEvent('tableUpdate'); }
    return $retval ?? $val;
  }

  // 执行查询并返回多条结果
  public function selectList() : ArrayList {
    $list = $this->getTableCache();
    if (!empty($this->_wheres)) {
      $wheres = $this->_wheres;
      $wheresArgs = $this->_wheresArgs;
      foreach ($wheres as $where) {
        if (str_ends_with($where, '` = ?') && preg_match('/`(\w+)` = \?/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] == $arg);
        } elseif (str_ends_with($where, '` > ?') && preg_match('/^`(\w+)` > \?$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] > $arg);
        } elseif (str_ends_with($where, '` < ?') && preg_match('/^`(\w+)` < \?$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] < $arg);
        } elseif (str_ends_with($where, '` >= ?') && preg_match('/^`(\w+)` >= \?$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] >= $arg);
        } elseif (str_ends_with($where, '` <= ?') && preg_match('/^`(\w+)` <= \?$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] <= $arg);
        } elseif (str_ends_with($where, '` <> ?') && preg_match('/^`(\w+)` <> \?$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] != $arg);
        } elseif (str_contains($where, '` IN (') && preg_match('/^`(\w+)` IN \(\?(?:, \?)*\)$/', $where, $m)) {
          $args = array_splice($wheresArgs, 0, substr_count($where, "?"));
          $list = $list->filter(fn($v) => in_array($v[$m[1]], $args));
        } elseif (str_contains($where, '` BETWEEN ') && preg_match('/^`(\w+)` BETWEEN \? AND \?$/', $where, $m)) {
          $args = array_splice($wheresArgs, 0, substr_count($where, "?"));
          $list = $list->filter(fn($v) => $v[$m[1]] >= $args[0] && $v[$m[1]] <= $args[1]);
        } elseif (str_ends_with($where, '` LIKE ?') && preg_match('/^`(\w+)` LIKE \?$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => str_contains($v[$m[1]], $arg));
        } elseif (str_ends_with($where, '` LIKE ?)') && preg_match('/^\(`(\w+)` = \? OR `(\w+)` LIKE \?)$/', $where, $m)) {
          $arg = array_shift($wheresArgs);
          $list = $list->filter(fn($v) => $v[$m[1]] == $arg || str_contains($v[$m[2]], $arg));
        } else {
          throw new LogicException("unsupported where syntax: \"$where\"");
        }
      }
    }
    if (!empty($this->_orders)) {
      $orders = ArrayList::new($this->_orders)->map(fn($v) => explode2(',', $v))->flat();
      $list->sort(function ($a, $b) use ($orders) {
        foreach ($orders as $order) {
          if (preg_match('/`(\w+)` DESC/', $order, $m)) {
            $v = $b[$m[1]] <=> $a[$m[1]];
          } elseif (preg_match('/`(\w+)` ASC/', $order, $m) || preg_match('/`(\w+)`/', $order, $m)) {
            $v = $a[$m[1]] <=> $b[$m[1]];
          } else {
            throw new LogicException('unsupported order syntax');
          }
          if ($v !== 0) {
            return $v;
          }
        }
        return 0;
      });
    }
    if ($this->_limit > -1 || $this->_offset > -1) {
      $limit = $this->_limit === -1 ? $list->count() : $this->_limit;
      $offset = $this->_offset === -1 ? 0 : $this->_offset;
      $list = $list->splice($offset, $limit);
    }
    if (!empty($this->_columns)) {
      $columns = $this->_columns;
      $list = $list->map(function($v) use ($columns) {
        $row = [];
        foreach ($columns as $column) {
          $column = trim($column, '`');
          $row[$column] = $v[$column];
        }
        return $row;
      });
    }
    return $list;
  }

  // 执行插入并返回结果, insertId 是自增 id 的值
  public function insert(bool $autoIncrement = true) : PDOStatement {
    return $this->refershTableCache(parent::insert($autoIncrement));
  }

  // 执行更新并返回结果, affected 是影响的行数
  public function update() : PDOStatement {
    return $this->refershTableCache(parent::update());
  }

  // 执行替换并返回结果, insertId 是自增 id 的值, affected 是影响的行数
  public function replace() : PDOStatement {
    return $this->refershTableCache(parent::replace());
  }

  // 执行删除并返回结果, affected 是影响的行数
  public function delete() : PDOStatement {
    return $this->refershTableCache(parent::delete());
  }

  // 统计总行数
  public function count() : int {
    return $this->countSql()->selectList()->count();
  }
}
