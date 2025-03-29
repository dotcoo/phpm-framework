<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace net\phpm\framework;

use \Swoole\Process;

final class Server {

  private static ?self $instance = null;

  public static function getInstance() : static {
    return static::$instance ?? static::$instance = new static();
  }

  private string $appRoot = '';

  private string $appSrc = '';

  private int $size = 0;

  private ?Process $process = null;

  private function __construct() {
    // $this->appRoot = str_replace(DIRECTORY_SEPARATOR, '/', realpath(__DIR__.'/../../../../'));
    $this->appRoot = realpath(__DIR__.'/../../../../');
    $this->appSrc = $this->appRoot . '/src';
  }

  public function reload() : void {
    if ($this->process != null) {
      $this->process->kill($this->process->pid, SIGTERM);
      $this->process = null;
    }
    $this->process = new Process(function() {
      define('APP_ENGINE', 'swoole');
      require realpath(__DIR__.'/../../../../public/index.php');
    });
    $this->process->start();
  }

  private function scandir(string $path) : array {
    if (!is_dir($path)) { return [$path]; }
    $files = [];
    foreach (scandir($path) as $file) {
      if ($file == '.' || $file == '..') { continue; }
      array_push($files, ...$this->scandir("$path/$file"));
    }
    return $files;
  }

  private function isChange() : bool {
    $size = $this->size;
    $this->size = 0;
    foreach ($this->scandir($this->appSrc) as $file) {
      $this->size += filesize($file);
    }
    return $this->size !== $size;
  }

  public function start() : never {
    while (true) {
      if ($this->isChange()) {
        $this->reload();
      }
      sleep(1);
    }
  }
}
