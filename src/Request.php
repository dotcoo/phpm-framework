<?php

declare(strict_types=1);

namespace net\phpm\framework;

use net\phpm\framework\interfaces\EventInterface;
use net\phpm\framework\traits\DynamicTrait;
use net\phpm\framework\traits\EventTrait;
use net\phpm\framework\exceptions\NotFoundException;
use net\phpm\framework\exceptions\VerifyException;
final class Request implements EventInterface {

  use DynamicTrait;
  use EventTrait;
  public string $method = 'GET';
  public string $uri = '';
  public string $url = '';
  public array $headersList = [];
  public array $headers = [];
  public string $contentType = '';
  public mixed $body = null;
  public array $server = [];
  public array $get = [];
  public array $post = [];
  public array $json = [];
  public array $files = [];
  public array $cookie = [];
  public array $session = [];
  public array $request = [];
  public string $routeUrl = '/';
  public bool $isEnded = false;
  public ?\Swoole\Http\Request $req = null;

  public function setMethod(string $method) : static {
    $this->method = $method;
    return $this;
  }

  public function getMethod() : string {
    return $this->method;
  }

  public function setUrl(string $url) : static {
    $this->url = $url;
    return $this;
  }

  public function getUrl() : string {
    return $this->url;
  }

  public function delHeader(string $name) : static {
    $name = strtolower($name);
    $this->headersList = array_filter($this->headersList, fn($v) => $v['name'] !== $name);
    unset($this->headers[$name]);
    return $this;
  }

  public function addHeader(string $name, string $value = '') : static {
    $name = strtolower($name);
    array_push($this->headersList, compact('name', 'value'));
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
    return $this;
  }

  public function getBody() : string {
    if ($this->body !== null) { return $this->body; }
    return $this->body = APP_ENGINE == 'swoole' ? $this->req->getContent() : file_get_contents('php://input');
  }

  public function header(string $name, string $value = '') : static {
    return $this->addHeader($name, $value);
  }
  public function end(bool $isEnded = true) : void {
    $this->isEnded = $isEnded;
  }
  public function ip() : string {
    return $this->server['REMOTE_ADDR'];
  }
  public function allowMethods(string ...$methods) : void {
    if (in_array($this->method, $methods)) { return; }
    throw new NotFoundException('request method not support!', 1, 1);
  }
  public static function fromFpmRequest() : static {
    $request = new static();
    $request->server = $_SERVER;
    foreach ($request->server as $name => $value) {
      if (str_starts_with($name, 'HTTP_')) {
        $request->header(substr($name, 5), $value);
      }
    }
    $request->get = $_GET;
    $request->post = $_POST;
    $request->files = $_FILES;
    $request->cookie = $_COOKIE;
    $request->request = $_REQUEST;
    return $request->fromInit();
  }
  public static function fromSwooleHttpRequest(\Swoole\Http\Request $req) : static {
    $request = new static();
    $request->req = $req;
    $request->server = [];
    foreach ($req->server as $name => $value) {
      $request->server[strtoupper($name)] = $value;
    }
    foreach ($req->header as $name => $value) {
      $request->server['HTTP_' . str_replace('-', '_', strtoupper($name))] = $value;
      $request->header(strtolower($name), $value);
    }
    $request->get = $req->get ?? [];
    $request->post = $req->post ?? [];
    $request->files = $req->files ?? [];
    $request->cookie = $req->cookie ?? [];
    $request->request = array_merge($request->get, $request->post);
    return $request->fromInit();
  }

  private function fromInit() : static {
    $this->method = $this->server['REQUEST_METHOD'];
    $this->uri = parse_url($this->server['REQUEST_URI'], PHP_URL_PATH);
    $this->url = substr($this->uri, strlen(APP_PUBLIC_URL));
    $this->json = empty($this->json) && ($this->server['REQUEST_METHOD'] === 'POST' || $this->server['REQUEST_METHOD'] === 'PUT') && str_starts_with($this->server['HTTP_CONTENT_TYPE']??'', 'application/json') ? json_decode($this->getBody(), true) : $this->json;
    return $this;
  }
  public string $moduleName = '';
  public string $controllerName = '';
  public string $methodName = '';
  public string $actionName = '';
  public function dataVerify(array $data, string $rules, int $depth = 1) : array {
    return Verify::paramsVerify($data, 'o{'.$rules.'}', depth: $depth);
  }

  public function requestVerify(string $rules, int $depth = 1) : array {
    return Verify::paramsVerify($this->request, 'o{'.$rules.'}', depth: $depth);
  }

  public function getVerify(string $rules, int $depth = 1) : array {
    return Verify::paramsVerify($this->get, 'o{'.$rules.'}', depth: $depth);
  }

  public function postVerify(string $rules, int $depth = 1) : array {
    return Verify::paramsVerify($this->post, 'o{'.$rules.'}', depth: $depth);
  }

  public function jsonVerify(string $rules, int $depth = 1) : array {
    return Verify::paramsVerify($this->json, 'o{'.$rules.'}', depth: $depth);
  }
}
