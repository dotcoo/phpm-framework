<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

// 根目录
$_ENV['APP_ROOT'] ??= rtrim(str_replace('\\', '/', dirname(__DIR__, 5)), '/') . '/';

// 环境
$_ENV['APP_ENV'] ??= 'production';

// DEBUG
$_ENV['APP_DEBUG'] ??= 'false';

// Swoole
$_ENV['APP_SWOOLE'] ??= 'false';

// Swoole Host
$_ENV['APP_SWOOLE_HOST'] ??= '0.0.0.0';

// Swoole Port
$_ENV['APP_SWOOLE_PORT'] ??= 8888;

// 监听文件更新
$_ENV['APP_RELOAD'] ??= 'false';

// 安全密钥
$_ENV['APP_SECRET'] ??= 'zay';

// 站点子目录
$_ENV['APP_URL'] ??= '';

// app目录
$_ENV['APP_SRC'] ??= 'src/';

// 站点根目录
$_ENV['APP_PUBLIC'] ??= 'public/';

// 上传目录
$_ENV['APP_UPLOAD'] ??= 'uploads/';

// 数据路径
$_ENV['APP_VAR'] ??= 'var/';

// 临时数据目录
$_ENV['APP_TMP'] ??= 'tmp/';

// 持久数据目录
$_ENV['APP_DATA'] ??= 'data/';

// 试图路径
$_ENV['APP_VIEW'] ??= 'view/';

// 日志目录
$_ENV['APP_LOG'] ??= 'logs/';

// 资源目录
$_ENV['APP_RES'] ??= 'res/';

// 字体目录
$_ENV['APP_FONT'] ??= 'fonts/';

// .env
foreach (parse_ini_file($_ENV['APP_ROOT'] . '.env.' . $_ENV['APP_ENV']) as $name => $value) {
  if (!str_starts_with($name, 'APP_')) { continue; }
  $_ENV[$name] = json_decode($value, true) ?? $value;
}
unset($name, $value);

// 根目录
defined('APP_ROOT') || define('APP_ROOT', $_ENV['APP_ROOT']);

// 环境
defined('APP_ENV') || define('APP_ENV', $_ENV['APP_ENV']);

// DEBUG
defined('APP_DEBUG') || define('APP_DEBUG', $_ENV['APP_DEBUG']);

// Swoole
defined('APP_SWOOLE') || define('APP_SWOOLE', $_ENV['APP_SWOOLE']);

// Swoole Host
defined('APP_SWOOLE_HOST') || define('APP_SWOOLE_HOST', $_ENV['APP_SWOOLE_HOST']);

// Swoole Port
defined('APP_SWOOLE_PORT') || define('APP_SWOOLE_PORT', $_ENV['APP_SWOOLE_PORT']);

// 监听文件更新
defined('APP_RELOAD') || define('APP_RELOAD', $_ENV['APP_RELOAD']);

// 安全密钥
defined('APP_SECRET') || define('APP_SECRET', $_ENV['APP_SECRET']);

// 站点子目录
defined('APP_URL') || define('APP_URL', $_ENV['APP_URL']);

// app目录
defined('APP_SRC') || define('APP_SRC', APP_ROOT . $_ENV['APP_SRC']);

// 站点根目录
defined('APP_PUBLIC') || define('APP_PUBLIC', APP_ROOT . $_ENV['APP_PUBLIC']);

// 上传目录
defined('APP_UPLOAD') || define('APP_UPLOAD', APP_PUBLIC . $_ENV['APP_UPLOAD']);

// 数据路径
defined('APP_VAR') || define('APP_VAR', APP_ROOT . $_ENV['APP_VAR']);

// 临时数据目录
defined('APP_TMP') || define('APP_TMP', APP_VAR . $_ENV['APP_TMP']);

// 持久数据目录
defined('APP_DATA') || define('APP_DATA', APP_VAR . $_ENV['APP_DATA']);

// 试图路径
defined('APP_VIEW') || define('APP_VIEW', APP_VAR . $_ENV['APP_VIEW']);

// 日志目录
defined('APP_LOG') || define('APP_LOG', APP_VAR . $_ENV['APP_LOG']);

// 资源目录
defined('APP_RES') || define('APP_RES', APP_ROOT . $_ENV['APP_RES']);

// 字体目录
defined('APP_FONT') || define('APP_FONT', APP_RES . $_ENV['APP_FONT']);
