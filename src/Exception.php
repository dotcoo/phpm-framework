<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use Throwable, LogicException;

class Exception extends LogicException {
  protected string $errorFile = '';

  protected int $errorLine = 0;

  protected string $errorColumn = '';

  public function __construct(string $message = '', int $code = 1, $depth = 0, $column = '', Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
    $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth + 1)[$depth];
    $this->errorFile = $stack['file'];
    $this->errorLine = $stack['line'];
    $this->errorColumn = $column;
  }

  public function setErrorFile(string $file) : void {
    $this->errorFile = $file;
  }

  public function getErrorFile() : string {
    return $this->errorFile ?: parent::getFile();
  }

  public function setErrorLine(int $line) : void {
    $this->errorLine = $line;
  }

  public function getErrorLine() : int {
    return $this->errorLine ?: parent::getLine();
  }

  public function setErrorColumn(string $column) : void {
    $this->errorColumn = $column;
  }

  public function getErrorColumn() : string {
    return $this->errorColumn;
  }
}
