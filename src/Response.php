<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use zay\interfaces\EventCenterInterface;
use zay\traits\Dynamic;
use zay\traits\EventCenter;
use zay\exceptions\ResponseEndException;

// Response 响应
final class Response implements EventCenterInterface {

  use Dynamic;
  use EventCenter;

  // 响应行
  public int $status = 200;
  public string $statusText = 'OK';

  // 响应头
  public array $headersList = [];
  public array $headers = [];

  // 响应体
  public mixed $body = '';

  // 响应数据
  public string $responseType = '';
  public array $cookiesList = [];
  public array $cookies = [];
  public array $json = [];

  // 是否结束
  public bool $isEnded = false;

  // Response
  public ?\Swoole\Http\Response $res = null;

  // 从php环境创建response对象
  public static function fromFpmResponse() : static {
    return new static();
  }

  // 从php环境创建response对象
  public static function fromSwooleHttpResponse(\Swoole\Http\Response $res) : static {
    $response = new static();
    $response->res = $res;
    return $response;
  }

  public function setStatus(int $status = 200, string $statusText = 'OK') : static {
    $this->status = $status;
    $this->statusText = $statusText;
    return $this;
  }

  public function getStatus() : int {
    return $this->status;
  }

  public function setStatusText(string $statusText = 'OK') : static {
    $this->statusText = $statusText;
    return $this;
  }

  public function getStatusText() : string {
    return $this->statusText;
  }

  public function delHeader(string $name) : static {
    $name = strtolower($name);
    $this->headersList = array_filter($this->headersList, fn($v) => $v['name'] !== $name);
    unsert($this->headers[$name]);
    return $this;
  }

  public function addHeader(string $name, string $value = '') : static {
    $name = strtolower($name);
    $this->headersList[] = compact('name', 'value');
    $this->headers[$name] = $value;
    return $this;
  }

  public function setHeader(string $name, string $value = '') : static {
    return $this->delHeader($name)->addHeader($name, $value);
  }

  public function getHeader(string $name, string $defval = '') : string {
    $name = strtolower($name);
    return $this->headers[$name] ?? $defval;
  }

  public function getHeaderAll(string $name) : array {
    $name = strtolower($name);
    return array_filter($this->headersList, fn($v) => $v['name'] === $name);
  }

  public function setBody(string $body) : static {
    $this->body = $body;
    return $this->body;
  }

  public function getBody() : string {
    return $this->body;
  }

  public function header(string $name, string $value = '') : static {
    return $this->addHeader($name, $value);
  }

  public function cookie(string $name, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false) : void {
    $this->cookiesList[] = compact('name', 'value', 'expire', 'path', 'domain', 'secure', 'httponly');
  }

  public function write(string $data) : void {
    $this->body .= $data;
  }

  public function send(string $data) : void {
    $this->body .= $data;
  }

  public function end(string $data = '') : void {
    $this->body .= $data;
    $this->isEnded = true;
    throw new ResponseEndException('response end');
  }

  public function json(mixed $data) : void {
    $this->end(json_encode_array($data));
  }

  public function message(string $errmsg = 'error', int $errno = 1, mixed $data = [], int $depth = 0) : void {
    $data['errno'] = $errno;
    $data['errmsg'] = $errmsg;
    if (APP_DEBUG && empty($data['errline'])) {
      $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth + 1)[$depth];
      $data['errfile'] = str_replace('\\', '/', $stack['file']);
      $data['errline'] = $stack['line'];
    }
    $this->header('content-type', 'application/json; charset=utf-8');
    $this->json($data);
  }

  public function errno(string $errmsg, int $errno = 1, mixed $data = [], int $depth = 0) : void {
    $this->message($errmsg, $errno, $data, $depth + 1);
  }

  public function error(string $errmsg = '', mixed $data = [], int $depth = 0) : void {
    $this->message($errmsg, 1, $data, $depth + 1);
  }

  public function success(string $errmsg = '', mixed $data = [], int $depth = 0) : void {
    $this->message($errmsg, 0, $data, $depth + 1);
  }

  private function sendFpmClient() : void {
    // 响应行
    http_response_code($this->status);
    // 响应头
    foreach ($this->headersList as $h) {
      header("{$h['name']}: {$h['value']}");
    }
    // cookiesList
    foreach ($this->cookiesList as $c) {
      setcookie($c['name'], $c['value'], $c['expire'], $c['path'], $c['domain'], $c['secure'], $c['httponly']);
    }
    // 响应体
    echo $this->body;
  }

  private function sendSwooleClient() : void {
    $res = $this->res;
    // 响应行
    $res->status($this->status);
    // 响应头
    foreach ($this->headersList as $h) {
      $res->header($h['name'], $h['value']);
    }
    // cookiesList
    foreach ($this->cookiesList as $c) {
      $res->cookie($c['name'], $c['value'], $c['expire'], $c['path'], $c['domain'], $c['secure'], $c['httponly']);
    }
    // 响应体
    $res->end($this->body);
  }

  public function sendResponse() : void {
    APP_SWOOLE ? $this->sendSwooleClient() : $this->sendFpmClient();
  }

  // context data
  public string $moduleName = '';
  public string $controllerName = '';
  public string $methodName = '';
  public string $actionName = '';

  // view
  public function view(array $data = [], string $action = '') : void {
    $this->write(View::render($data, $action ?: $this->actionName));
  }

  public function render(array $data = [], string $action = '') : string {
    return View::render($data, $action ?: $this->actionName);
  }
}
