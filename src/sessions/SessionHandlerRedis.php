<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\sessions;

class SessionHandlerRedis implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface {

  public string $save_handler = '';

  public string $save_path = '';

  public string $sid = '';

  public bool $isNew = false;

  public string $prefix = 'PHPREDIS_SESSION';

  public function open(string $save_path, string $save_handler): bool {
    // echo "open: $save_path, $save_handler<br>\n";
    $this->save_path = $save_path;
    $this->save_handler = $save_handler;
    return true;
  }

  public function create_sid(): string {
    // echo "create_sid: <br>\n";
    $this->isNew = true;
    return bin2hex(random_bytes(30));
  }

  public function redis() : \Redis {
    // TODO 连接池
    $paths = explode(',', $this->save_path);
    $path = $paths[0];
    $path = trim($path);
    $url = array_merge(['host' => '127.0.0.1', 'port' => '6379', 'query' => ''], parse_url($path));
    parse_str($url['query'] ?? '', $query);
    $query = array_merge(['weight' => 1, 'timeout' => 0, 'persistent' => 0, 'prefix' => 'PHPREDIS_SESSION', 'auth' => '', 'database' => '0'], $query);
    $redis = new \Redis();
    // TODO 暂时不支持持久连技
    // $query['persistent'] ? $redis->pconnect($url['host'], $url['port'], $query['timeout']) : $redis->connect($url['host'], $url['port'], $query['timeout']);
    $redis->connect($url['host'], $url['port'], $query['timeout']);
    $this->prefix = $query['prefix'];
    !empty($query['auth']) && $redis->auth($query['auth']);
    !empty($query['database']) && $redis->select($query['database']);
    return $redis;
  }

  public function read(string $sid): string|false {
    // echo "read: $sid<br>\n";
    $this->sid = $sid;
    return $this->redis()->get("{$this->prefix}{$this->sid}") ?: '';
  }

  public function write(string $sid, string $data): bool {
    // echo "write: $sid, $data<br>\n";
    return $this->redis()->setEx("{$this->prefix}{$this->sid}", APP_SESSION_GC_MAXLIFETIME, $data);
  }

  public function destroy(string $sid): bool {
    // echo "destroy: $sid<br>\n";
    return $this->redis()->del("{$this->prefix}{$this->sid}");
  }

  public function close(): bool {
    // echo "close: <br>\n";
    return true;
  }

  public function gc(int $max_lifetime): int|false {
    // echo "gc: $max_lifetime<br>\n";
    return 1;
  }

  public function updateTimestamp(string $sid, string $data): bool {
    // echo "updateTimestamp: $sid<br>\n";
    // TODO 重新设置ttl
    return true;
  }

  public function validateId(string $sid): bool {
    // echo "validateId: $sid<br>\n";
    return true;
  }
}
