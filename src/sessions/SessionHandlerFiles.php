<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay\sessions;

use SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface;

class SessionHandlerFiles implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {

  public string $save_handler = '';

  public string $save_path = '';

  public string $sid = '';

  public bool $isNew = false;

  public string $data = '';

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

  public function read(string $sid): string|false {
    // echo "read: $sid<br>\n";
    $this->sid = $sid;
    return file_exists(APP_SESSION_SAVE_PATH . "sess_$sid") ? file_get_contents(APP_SESSION_SAVE_PATH . "sess_$sid") : '';
  }

  public function write(string $sid, string $data): bool {
    // echo "write: $sid, $data<br>\n";
    file_put_contents(mkdir2(APP_SESSION_SAVE_PATH . "sess_$sid"), $data);
    return true;
  }

  public function destroy(string $sid): bool {
    // echo "destroy: $sid<br>\n";
    return file_exists(APP_SESSION_SAVE_PATH . "sess_$sid") ? unlink(APP_SESSION_SAVE_PATH . "sess_$sid") : true;
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
    return true;
  }

  public function validateId(string $sid): bool {
    // echo "validateId: $sid<br>\n";
    return true;
  }
}
