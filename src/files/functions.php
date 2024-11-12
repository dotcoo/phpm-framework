<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

function camel2under(string $name) : string {
  static $caches = [];
  return $caches[$name] ?? $caches[$name] = strtolower(preg_replace('/(\B[A-Z])/', '_\\1', $name));
}

function under2camel(string $name) : string {
  static $caches = [];
  return $caches[$name] ?? $caches[$name] = lcfirst(str_replace('_', '', ucwords($name, '_')));
  // return $caches[$name] ?? $caches[$name] = preg_replace_callback('/_([a-z])/', fn($m) => strtoupper($m[1]), $name);
}

function camel2pascal(string $name) : string {
  static $caches = [];
  return $caches[$name] ?? $caches[$name] = ucfirst($name);
}

function pascal2camel(string $name) : string {
  static $caches = [];
  return $caches[$name] ?? $caches[$name] = lcfirst($name);
}

function camel2kebab(string $name) : string {
  static $caches = [];
  return $caches[$name] ?? $caches[$name] = strtolower(preg_replace('/(\B[A-Z])/', '-\\1', $name));
}

function kebab2camel(string $name) : string {
  static $caches = [];
  return $caches[$name] ?? $caches[$name] = lcfirst(str_replace('-', '', ucwords($name, '-')));
  // return $caches[$name] ?? $caches[$name] = preg_replace_callback('/-([a-z])/', fn($m) => strtoupper($m[1]), $name);
}

function dirtail(string $dir) : string {
  static $caches = [];
  return $caches[$dir] ?? $caches[$dir] = rtrim($dir, '/') . '/';
}

function class2path(string $className) : string {
  static $caches = [];
  return $caches[$className] ?? $caches[$className] = str_replace('\\', '/', APP_SRC . substr($className, 4) . '.php');
}

function class2dir(string $className, int $levels = 1) : string {
  static $caches = [];
  return $caches["$className,$levels"] ?? $caches["$className,$levels"] = dirname(class2path($className), $levels) . '/';
}

function class2package(string $className) : string {
  static $caches = [];
  return $caches[$className] ?? $caches[$className] = substr($className, 0, strrpos($className, '\\'));
}

function class2class(string $className) : string {
  static $caches = [];
  return $caches[$className] ?? $caches[$className] = substr($className, strrpos($className, '\\') + 1);
}

function action2url(string $action) : string {
  static $caches = [];
  if (array_key_exists($action, $caches)) { return $caches[$action]; }
  [$module, $controller, $method] = explode('.', $action);
  return $caches[$action] = '/' . camel2kebab($module) . '/' . camel2kebab($controller) . '/' . camel2kebab($method);
}

function action2moduleClass(string $action) : string {
  static $caches = [];
  if (array_key_exists($action, $caches)) { return $caches[$action]; }
  [$module] = explode('.', $action);
  return $caches[$action] = '\\app\\modules\\'.$module.'\\Module';
}

function action2controllerClass(string $action) : string {
  static $caches = [];
  if (array_key_exists($action, $caches)) { return $caches[$action]; }
  [$module, $controller] = explode('.', $action);
  return $caches[$action] = '\\app\\modules\\'.$module.'\\controllers\\'.camel2pascal($controller).'Controller';
}

function action2viewSource(string $action) : string {
  static $caches = [];
  if (array_key_exists($action, $caches)) { return $caches[$action]; }
  [$module, $controller, $method] = explode('.', $action);
  return $caches[$action] = APP_SRC.'modules/'.$module.'/views/'.camel2under($controller).'/'.camel2under($method).'.view.php';
}

function action2viewTarget(string $action) : string {
  static $caches = [];
  if (array_key_exists($action, $caches)) { return $caches[$action]; }
  [$module, $controller, $method] = explode('.', $action);
  return $caches[$action] = APP_VIEW.$module.'/'.camel2under($controller).'/'.camel2under($method).'.swoole.php';
}

/*
function array_assign(mixed ...$args) : mixed {
  $a = $args[0];
  for ($i = 1; $i < count($args); $i++) {
    $b = $args[$i];
    if (is_array($a) && array_is_list($a) && is_array($b) && array_is_list($b)) {
      foreach ($b as $key => $val) {
        $a[] = $val;
      }
    } elseif (is_array($a) && is_array($b)) {
      foreach ($b as $key => $val) {
        $a[$key] = array_assign($a[$key], $val);
      }
    } else {
      $a = $b;
    }
  }
  return $a;
}
*/
