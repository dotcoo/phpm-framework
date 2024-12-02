<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use Closure;

use zay\exceptions\ViewException;

final class View {

  public static App $app;

  public static array $views = [];

  public static function compile(string $source, string $target, string $handler) : void {
    if (file_exists($target) && filemtime($source) < filemtime($target)) { return; }
    $code = file_get_contents($source);
    $code = preg_replace("/\\r+/",                                          "",                                                                                 $code);
    $code = preg_replace("/\\n+/",                                          "\n",                                                                               $code);
    $code = substr(preg_replace_callback("/\?>(.*?)<\?/ms",                 fn(array $m) => str_replace("'", "\\'", $m[0]), "?>$code<?"), 2, -2);
    $code = preg_replace("/<\?=view\(['\"](.+?)['\"], ?(.+?)\)\?>/",        "' . \\zay\\View::view(\\zay\\View::relative(\$___handler, '\\1'), \2) . '",        $code);
    $code = preg_replace("/<\?=view\(['\"](.+?)['\"]\)\?>/",                "' . \\zay\\View::view(\\zay\\View::relative(\$___handler, '\\1'), \$___data) . '", $code);
    $code = preg_replace("/<\?=html\((.+?)\)\?>/",                          "' . strval($1) . '",                                                               $code);
    $code = preg_replace("/<\?=(.+?)\?>/",                                  "' . htmlspecialchars(strval($1)) . '",                                             $code);
    $code = preg_replace_callback("/htmlspecialchars\(strval(\(.+?)\)\);/", Closure::fromCallable([static::class, "variables_callback"]),                       $code);
    $code = preg_replace("/\n?\s*<\?php/",                                  "';",                                                                               $code);
    $code = preg_replace("/\?>\s*\n?/",                                     "\$___html .= '",                                                                   $code);
    file_put_contents(mkdir2($target), "<?php\nreturn function(array \$___data = []) : string {\nextract(\$___data);\n\$___handler = '$handler';\n\$___html = '$code';\nreturn \$___html;\n};\n");
  }

  public static function view(string $handler, array $data) : string {
    if (APP_ENGINE == 'fpm' && !array_key_exists($handler, static::$views)) {
      [$m, $c, $t] = explode('/', ltrim($handler, '/'));
      if (!array_key_exists($m, static::$app->moduleNames)) { throw new ViewException("view {$handler} not found!"); }
      $module = static::$app->moduleNames[$m];
      $source = $module->path.'/views/'.$c.'/'.$t.'.view.php';
      $target = APP_VIEW.'/'.$module->fullname.'/'.$c.'/'.$t.'.php';
      if (!file_exists($source)) { throw new ViewException("view {$handler} not found!"); }
      if (!file_exists($target) || filemtime($source) > filemtime($target)) {
        static::compile($source, $target, $handler);
      }
      static::$views[$handler] = require $target;
    }
    if (!array_key_exists($handler, static::$views)) { throw new ViewException("view {$handler} not found!"); }
    return static::$views[$handler]($data);
  }

  public static $pipes = [];

  public static function variables_callback(array $m) : string {
    $code = str_replace("||", "\0\0", $m[1]);
    $code = static::variables_pipe($code);
    return "htmlspecialchars(strval(" . str_replace("\0\0", "||", $code) . "));";
  }

  public static function variables_pipe($code) {
    $pipes = array_map("trim", explode("|", $code));
    $code = array_shift($pipes);
    foreach ($pipes as $pipe) {
      list($func, $args) = array_merge(explode("(", $pipe, 2), [""]);
      // if ($func == "expr") {
      //   $code = sprintf((strpos($args, "%s") === false ? "%s " : "") . $args, $code);
      //   continue;
      // }
      $args = array_map("trim", empty($args) ? array() : explode(",", $args));
      $args = empty($args) ? "" : ", " . implode(", ", $args);
      if (array_key_exists($func, static::$pipes)) {
        $code = sprintf("\\zay\\View::\$pipes['%s'](%s%s", $func, $code, $args);
      } else {
        $code = sprintf("%s(%s%s)", $func, $code, $args);
      }
    }
    return $code;
  }

  public static function relative(string $current, string $handler = "") : string {
    if (str_starts_with($handler, "/")) {
      return $handler;
    } elseif ($handler == "") {
      return $current;
    } else {
      return dirname($current, str_contains($handler, "/") ? 2 : 1)."/".$handler;
    }
  }
}

View::$pipes["date"] = fn(int $time, string $format) : string => date($format, $time);
