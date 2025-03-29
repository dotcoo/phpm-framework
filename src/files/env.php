<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

// 根目录
defined('APP_ROOT') || define('APP_ROOT', dirname(__DIR__, 5));

// .env
foreach (parse_ini_file(APP_ROOT.'/.env') as $name => $value) {
  if (!str_starts_with($name, 'APP_')) { continue; }
  $_ENV[$name] = json_decode($value, true) ?? $value;
}
unset($name, $value);

// 引擎
defined('APP_ENGINE') || define('APP_ENGINE', $_ENV['APP_ENGINE'] ?? (PHP_SAPI == 'cli' && phpversion('swoole') !== false ? 'swoole' : 'fpm'));

// 服务器
defined('APP_SWOOLE_HOST') || define('APP_SWOOLE_HOST', $_ENV['APP_SWOOLE_HOST'] ?? '0.0.0.0');

// 端口
defined('APP_SWOOLE_PORT') || define('APP_SWOOLE_PORT', ($_ENV['APP_SWOOLE_PORT'] ?? '8888') - 0);

// 环境
defined('APP_ENV') || define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

// 调试
defined('APP_DEBUG') || define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') == 'true');

// 安全密钥
defined('APP_SECRET') || define('APP_SECRET', $_ENV['APP_SECRET'] ?? 'phpm');

// app目录
defined('APP_SRC') || define('APP_SRC', APP_ROOT.($_ENV['APP_SRC'] ?? '/src'));

// 数据路径
defined('APP_VAR') || define('APP_VAR', APP_ROOT.($_ENV['APP_VAR'] ?? '/var'));

// 临时数据目录
defined('APP_TMP') || define('APP_TMP', APP_VAR.($_ENV['APP_TMP'] ?? '/tmp'));

// 持久数据目录
defined('APP_DATA') || define('APP_DATA', APP_VAR.($_ENV['APP_DATA'] ?? '/data'));

// 试图路径
defined('APP_VIEW') || define('APP_VIEW', APP_VAR.($_ENV['APP_VIEW'] ?? '/views'));

// 日志目录
defined('APP_LOG') || define('APP_LOG', APP_VAR.($_ENV['APP_LOG'] ?? '/logs'));

// 资源目录
defined('APP_RES') || define('APP_RES', APP_ROOT.($_ENV['APP_RES'] ?? '/res'));

// 字体目录
defined('APP_FONT') || define('APP_FONT', APP_RES.($_ENV['APP_FONT'] ?? '/font'));

// 站点根目录
defined('APP_PUBLIC') || define('APP_PUBLIC', APP_ROOT.($_ENV['APP_PUBLIC'] ?? '/public'));

// 站点子目录
defined('APP_PUBLIC_URL') || define('APP_PUBLIC_URL', $_ENV['APP_PUBLIC_URL'] ?? '');

// 上传目录
defined('APP_UPLOAD') || define('APP_UPLOAD', APP_PUBLIC.($_ENV['APP_UPLOAD'] ?? '/uploads'));

// 站点子目录
defined('APP_UPLOAD_URL') || define('APP_UPLOAD_URL', $_ENV['APP_UPLOAD_URL'] ?? APP_PUBLIC_URL.'/uploads');
