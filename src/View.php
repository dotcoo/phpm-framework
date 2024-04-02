<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use Closure;
use zay\exceptions\ViewException;

final class View {

  private static array $renders = [];

  public static function compile(string $source, string $target) : void {
    $code = file_get_contents($source);
    $code = preg_replace('/\\r+/',         '',              $code);
    $code = substr(preg_replace_callback('/\?>(.*?)<\?/ms', fn(array $m) => str_replace('\'', '\\\'', $m[0]), "?>$code<?"), 2, -2);
    // $code = preg_replace('/\'/',           '\\\'',          $code);
    /*$code = preg_replace('/<\?php require "(.+?)"; \?>/', '\' . (require \zay\View::compileSwoole("'.pathinfo($source, PATHINFO_DIRNAME).'/$1")) . \'', $code);*/
    $code = preg_replace('/<\?=render\((.*), .+?\)\?>/', '\' . htmlspecialchars(strval($1)) . \'', $code);
    $code = preg_replace('/<\?=render(.+?)\?>/', '\' . htmlspecialchars(strval($1)) . \'', $code);
    $code = preg_replace('/<\?=(.+?)\?>/', '\' . htmlspecialchars(strval($1)) . \'', $code);
    $code = preg_replace_callback('/htmlspecialchars\(strval(\(.+?)\)\);/', Closure::fromCallable([static::class, 'variables_callback']), $code);
    $code = preg_replace('/\\n{2,}/',      "\n",            $code);
    $code = preg_replace('/\n?\s*<\?php/', '\';',           $code);
    $code = preg_replace('/\?>\s*\n?/',    '$___html .= \'', $code);
    file_put_contents(mkdir2($target), "<?php\nreturn function(array \$___data) : string {\nextract(\$___data);\n\$___html = '$code';\nreturn \$___html;\n};\n");
  }

  public static function render(array $data, string $action) : string {
    $source = action2viewSource($action);
    $target = action2viewTarget($action);
    if (!file_exists($source)) { throw new ViewException("view {$source} not found!"); }
    if (!APP_DEBUG && file_exists($target) && filemtime($source) < filemtime($target) && array_key_exists($action, self::$renders)) { return self::$renders[$action]($data); }
    self::compile($source, $target);
    self::$renders[$action] = require $target;
    return self::$renders[$action]($data);
  }

  public static $pipes = [];

  public static function variables_callback(array $m) : string {
    $code = str_replace('||', "\0\0", $m[1]);
    $code = static::variables_pipe($code);
    return 'htmlspecialchars(strval(' . str_replace("\0\0", '||', $code) . '));';
  }

  public static function variables_pipe($code) {
    $pipes = array_map('trim', explode('|', $code));
    $code = array_shift($pipes);
    foreach ($pipes as $pipe) {
      list($func, $args) = array_merge(explode('(', $pipe, 2), ['']);
      // if ($func == 'expr') {
      //   $code = sprintf((strpos($args, '%s') === false ? '%s ' : '') . $args, $code);
      //   continue;
      // }
      $args = array_map('trim', empty($args) ? array() : explode(',', $args));
      $args = empty($args) ? '' : ', ' . implode(', ', $args);
      if (array_key_exists($func, static::$pipes)) {
        $code = sprintf('\zay\View::$pipes[\'%s\'](%s%s', $func, $code, $args);
      } else {
        $code = sprintf('%s(%s%s)', $func, $code, $args);
      }
    }
    return $code;
  }
}

View::$pipes['date'] = fn(int $time, string $format) : string => date($format, $time);
