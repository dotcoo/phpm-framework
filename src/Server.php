<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework;

require_once __DIR__ . '/files/env.php';

final class Server {

  private static ?self $instance = null;

  public static function getInstance() : static {
    return static::$instance ?? static::$instance = new static();
  }

  private array $dirs = [APP_SRC];

  private int $size = 0;

  private ?\Swoole\Process $process = null;

  private function __construct() {}

  public function watch(string ...$dirs) : void {
    array_push($this->dirs, ...$dirs);
  }

  public function reload() : void {
    if ($this->process != null) {
      $this->process->kill($this->process->pid, SIGTERM);
      $this->process = null;
    }
    $this->process = new \Swoole\Process(function() {
      defined('APP_ENGINE') || define('APP_ENGINE', 'swoole');
      require realpath(APP_PUBLIC.'/index.php');
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
    foreach ($this->dirs as $dir) {
      foreach ($this->scandir($dir) as $file) {
        $this->size += filesize($file);
      }
    }
    return $this->size !== $size;
  }

  public function start() : never {
    while (true) {
      if ($this->isChange()) {
        echo sprintf("%s reload\n", date('Y-m-d H:i:s'));
        $this->reload();
      }
      sleep(1);
    }
  }
}
