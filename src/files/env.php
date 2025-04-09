<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);
defined('APP_ROOT') || define('APP_ROOT', dirname(__DIR__, 5));
foreach (parse_ini_file(APP_ROOT.'/.env') as $name => $value) {
  if (!str_starts_with($name, 'APP_')) { continue; }
  $_ENV[$name] = json_decode($value, true) ?? $value;
}
unset($name, $value);
defined('APP_ENGINE') || define('APP_ENGINE', $_ENV['APP_ENGINE'] ?? (PHP_SAPI == 'cli' && phpversion('swoole') !== false ? 'swoole' : 'fpm'));
defined('APP_SWOOLE_HOST') || define('APP_SWOOLE_HOST', $_ENV['APP_SWOOLE_HOST'] ?? '0.0.0.0');
defined('APP_SWOOLE_PORT') || define('APP_SWOOLE_PORT', ($_ENV['APP_SWOOLE_PORT'] ?? '8888') - 0);
defined('APP_ENV') || define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
defined('APP_DEBUG') || define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? '0') == '1');
defined('APP_SECRET') || define('APP_SECRET', $_ENV['APP_SECRET'] ?? 'phpm');
defined('APP_SRC') || define('APP_SRC', APP_ROOT.($_ENV['APP_SRC'] ?? '/src'));
defined('APP_VAR') || define('APP_VAR', APP_ROOT.($_ENV['APP_VAR'] ?? '/var'));
defined('APP_TMP') || define('APP_TMP', APP_VAR.($_ENV['APP_TMP'] ?? '/tmp'));
defined('APP_DATA') || define('APP_DATA', APP_VAR.($_ENV['APP_DATA'] ?? '/data'));
defined('APP_VIEW') || define('APP_VIEW', APP_VAR.($_ENV['APP_VIEW'] ?? '/views'));
defined('APP_LOG') || define('APP_LOG', APP_VAR.($_ENV['APP_LOG'] ?? '/logs'));
defined('APP_RES') || define('APP_RES', APP_ROOT.($_ENV['APP_RES'] ?? '/res'));
defined('APP_FONT') || define('APP_FONT', APP_RES.($_ENV['APP_FONT'] ?? '/font'));
defined('APP_PUBLIC') || define('APP_PUBLIC', APP_ROOT.($_ENV['APP_PUBLIC'] ?? '/public'));
defined('APP_PUBLIC_URL') || define('APP_PUBLIC_URL', $_ENV['APP_PUBLIC_URL'] ?? '');
defined('APP_UPLOAD') || define('APP_UPLOAD', APP_PUBLIC.($_ENV['APP_UPLOAD'] ?? '/uploads'));
defined('APP_UPLOAD_URL') || define('APP_UPLOAD_URL', $_ENV['APP_UPLOAD_URL'] ?? APP_PUBLIC_URL.'/uploads');
