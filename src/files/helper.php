<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

// if (PHP_VERSION_ID < 80000) {
//   function str_starts_with(string $haystack, string $needle) : bool {
//     return strpos($haystack, $needle) === 0;
//   }

//   function str_contains(string $haystack, string $needle) : bool {
//     return strpos($haystack, $needle) !== false;
//   }

//   function str_ends_with(string $haystack, string $needle) : bool {
//     return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
//   }
// }

// if (PHP_VERSION_ID < 80100) {
//   function array_is_list(array $array) : bool {
//     for ($l = count($array), $i = 0; $i < $l; $i++) {
//       if (!array_key_exists($i, $array)) { return false; }
//     }
//     return true;
//   }
// }

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

function env(string $name, mixed $defval = null) : bool|int|float|string|array|object {
  return $_ENV[$name] ?? $defval;
}

function explode2(string $separator, ?string $string, null|string|Closure $map = null) : array {
  return empty($string) ? [] : (empty($map) ? explode($separator, $string) : array_map($map, explode($separator, $string)));
}

function implode2(string $separator, array $array) : string {
  return empty($array) ? '' : implode($separator, $array);
}

function json_encode2(mixed $value) : string {
  return json_encode($value, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function json_decode2(?string $value) : mixed {
  return json_decode($value, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY);
}

function json_encode_array(mixed $value) : string {
  return json_encode($value, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function json_decode_array(string $value) : ?array {
  return json_decode($value, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY);
}

function json_encode_object(mixed $value) : string {
  return json_encode($value, JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT);
}

function json_decode_object(string $value) : ?object {
  return json_decode($value, true, 512, JSON_INVALID_UTF8_IGNORE | JSON_BIGINT_AS_STRING);
}

function str_rand(int $len = 8, string $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') : string {
  $s = '';
  for ($l = strlen($char), $i = 0; $i < $len; $i++) {
    $s .= $char[random_int(0, $l - 1)];
  }
  return $s;
}

function filename(string $filename) : string {
  return pathinfo($filename, PATHINFO_BASENAME);
}

function filebase(string $filename) : string {
  return pathinfo($filename, PATHINFO_FILENAME);
}

function fileext(string $filename) : string {
  return pathinfo($filename, PATHINFO_EXTENSION);
}

function mkdir2(string $filename) : string {
  $dirname = dirname($filename);
  if (!file_exists($dirname)) {
    mkdir($dirname, 0755, true);
  }
  return $filename;
}

function scanfile2(string $path, string $dir = '', array &$files = []) : array {
  if (!file_exists("$path$dir")) { return $files; }
  foreach (scandir("$path$dir") as $file) {
    if ($file == '.' || $file == '..' || !is_file("$path$dir/$file")) { continue; }
    array_push($files, $file);
  }
  return $files;
}

function scandir2(string $path, string $dir = '', array &$files = []) : array {
  if (!file_exists("$path$dir")) { return $files; }
  foreach (scandir("$path$dir") as $file) {
    if ($file == '.' || $file == '..' || !is_dir("$path$dir/$file")) { continue; }
    array_push($files, $file);
  }
  return $files;
}

function scanfile3(string $path, string $dir = '', array &$files = []) : array {
  if (!file_exists("$path$dir")) { return $files; }
  $recursion = function(string $path, string $dir = '', array &$files = []) use (&$recursion) : array {
    foreach (scandir("$path$dir") as $file) {
      if ($file == '.' || $file == '..') { continue; }
      is_dir("$path$dir/$file") ? $recursion($path, "$dir/$file", $files) : array_push($files, "$dir/$file");
    }
    return $files;
  };
  return $recursion($path, $dir, $files);
}

function log_args(mixed ...$args) : array {
  foreach ($args as $i => $a) {
    $type = gettype($a);
    switch ($type) {
      case 'integer':
      case 'double':
      case 'string':
      case 'boolean':
      case 'array':
      case 'object':
        $args[$i] = json_encode_array($a);
        break;
      case 'NULL':
      case 'resource':
      case 'resource (closed)':
      case 'unknown type':
      default:
        $args[$i] = $type;
        break;
    }
  }
  unset($a);
  return $args;
}

function log_debug(mixed ...$args) : void {
  file_put_contents(mkdir2(APP_LOG . '/' . date('Ymd') . '.log'), date('Y-m-d H:i:s ') . implode(', ', log_args(...$args)) . "\n", FILE_APPEND);
}

function log_debugf(string $format, mixed ...$args) : void {
  file_put_contents(mkdir2(APP_LOG . '/' . date('Ymd') . '.log'), date('Y-m-d H:i:s ') . sprintf($format, ...log_args(...$args)) . "\n", FILE_APPEND);
}

function log_debug_return(mixed $retval, mixed ...$args) : mixed {
  log_debug(...$args); return $retval;
}

function log_debugf_return(mixed $retval, string $format, mixed ...$args) : mixed {
  log_debugf($format, ...$args); return $retval;
}

function thumbnail(string $file, int $new_w = 200, $new_h = 200, bool $in = false) : string {
  if (!file_exists($file)) { throw new LogicException('图片不存在!'); }
  $imgcreatefunc = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_JPEG2000 => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
  $imgsavefunc = [IMAGETYPE_JPEG => 'imagejpeg', IMAGETYPE_JPEG2000 => 'imagejpeg', IMAGETYPE_PNG => 'imagepng', IMAGETYPE_WEBP => 'imagewebp'];
  list($src_w, $src_h, $src_type) = getimagesize($file);
  if (!array_key_exists($src_type, $imgcreatefunc)) { throw new LogicException('图片的类型不支持!'); }
  $src_image = $imgcreatefunc[$src_type]($file);
  if($src_w > $new_w || $src_h > $new_h){
    $scale_w = $new_w / $src_w;
    $scale_h = $new_h / $src_h;
    $b = $in ? ($scale_w < $scale_h) : !($scale_w < $scale_h);
    $scale = ($b) ? $scale_w : $scale_h;
    $zoom_w = intval($src_w * $scale);
    $zoom_h = intval($src_h * $scale);
  }else{
    $zoom_w = $src_w;
    $zoom_h = $src_h;
  }
  $dst_image = imagecreatetruecolor($zoom_w, $zoom_h);
  imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $zoom_w, $zoom_h, $src_w, $src_h);
  $img_file = fopen('php://memory', 'r+');
  $imgsavefunc[$src_type]($dst_image, $img_file);
  imagedestroy($dst_image);
  imagedestroy($src_image);
  rewind($img_file);
  $data = stream_get_contents($img_file);
  fclose($img_file);
  return $data;
}

function imagecut(string $file, int $new_w = 200, int $new_h = 200, int $pos = 0) : string {
  if (!file_exists($file)) { throw new LogicException('图片不存在!'); }
  $imgcreatefunc = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_JPEG2000 => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
  $imgsavefunc = [IMAGETYPE_JPEG => 'imagejpeg', IMAGETYPE_JPEG2000 => 'imagejpeg', IMAGETYPE_PNG => 'imagepng', IMAGETYPE_WEBP => 'imagewebp'];
  list($src_w, $src_h, $src_type) = getimagesize($file);
  if (!array_key_exists($src_type, $imgcreatefunc)) { throw new LogicException('图片的类型不支持!'); }
  $src_image = $imgcreatefunc[$src_type]($file);
  if($src_w > $new_w || $src_h > $new_h){
    $scale_w = $new_w / $src_w;
    $scale_h = $new_h / $src_h;
    $scale = $scale_w > $scale_h ? $scale_w : $scale_h;
    $zoom_w = intval($src_w * $scale);
    $zoom_h = intval($src_h * $scale);
    $zoom_w_cut = $zoom_w < $new_w ? $zoom_w : $new_w;
    $zoom_h_cut = $zoom_h < $new_h ? $zoom_h : $new_h;
    $src_w_cut = intval($zoom_w_cut / $zoom_w * $src_w);
    $src_h_cut = intval($zoom_h_cut / $zoom_h * $src_h);
  } else {
    $src_w_cut = $zoom_w_cut = $src_w;
    $src_h_cut = $zoom_h_cut = $src_h;
  }
  $dst_image = imagecreatetruecolor($zoom_w_cut, $zoom_h_cut);
  imagecopyresampled($dst_image, $src_image, 0, 0, ($src_w - $src_w_cut) / 2 * ($pos % 3), ($src_h - $src_h_cut) / 2 * intval($pos / 3), $zoom_w_cut, $zoom_h_cut, $src_w_cut, $src_h_cut);
  $img_file = fopen('php://memory', 'r+');
  $imgsavefunc[$src_type]($dst_image, $img_file);
  imagedestroy($dst_image);
  imagedestroy($src_image);
  rewind($img_file);
  $data = stream_get_contents($img_file);
  fclose($img_file);
  return $data;
}

function isColorful(int $r, int $g, int $b, int $diff = 160) : bool {
  return max(abs($r - $b), abs($g - $r), abs($b - $g)) >= $diff;
}

function colorfulRand(int $diff = 160) : array {
  while (true) {
    $r = random_int(0, 255); $g = random_int(0, 255); $b = random_int(0, 255);
    if (isColorful($r, $g, $b, $diff)) { return [$r, $g, $b]; }
  }
}

function captcha(string $code, int $width = 80, int $height = 30, int $x = 12, int $y = 3, int $font_width = 14, int $font_height = 18, bool $rotate = true) : string {
  $font_file = APP_FONTS . 'arial.ttf';
  $font_size = $font_height;
  $ty = $y + $font_size;
  $dst_image = imagecreatetruecolor($width, $height);
  imagefill($dst_image, 0, 0, imagecolorallocate($dst_image, 255, 255, 255));
  if ($rotate) {
    $tx = $x;
    foreach(str_split($code) as $c) {
      $angle = mt_rand(340, 380) % 360;
      imagettftext($dst_image, $font_size, $angle, $tx, $ty, imagecolorallocate($dst_image, ...colorfulRand()), $font_file, $c);
      $tx += $font_width;
    }
  } else {
    imagettftext($dst_image, $font_size, 0, $x, $ty, $font_color, $font_file, $code);
  }
  $img_file = fopen('php://memory', 'r+');
  imagepng($dst_image, $img_file);
  imagedestroy($dst_image);
  rewind($img_file);
  $data = stream_get_contents($img_file);
  fclose($img_file);
  return $data;
}

function list2tree(array $list, mixed $pid = 0, string $pidKey = 'pid', string $idKey = 'id') : array {
  $tree = [];
  foreach ($list as $value) {
    if ($value[$pidKey] !== $pid) { continue; }
    $value['children'] = list2tree($list, $value[$idKey], $pidKey, $idKey);
    array_push($tree, $value);
  }
  return $tree;
}

// tinyflake: time 32bit + sn 21bit
function tinyflake(?int $time = null) : int {
  static $t = 0, $r = 0;
  $time = $time ?? time();
  if ($t !== $time) { $t = $time; $r = 0; }
  return (($t - 1704067200) << 21) + ($r++ & 0x1FFFFF);
};

function var_state(mixed $data, int $indent = 0) : string {
  switch (gettype($data)) {
    case 'boolean':
      return $data ? 'true' : 'false';
    case 'integer':
    case 'double':
      return $data . '';
    case 'string':
      return json_encode_any($data);
    case 'array':
      $code = "[\n";
      foreach ($data as $key => $val) {
        $code .= sprintf("%s%s => %s,\n", str_repeat('  ', $indent + 1), json_encode_any($key), var_state($val, $indent + 1));
      }
      $code .= str_repeat('  ', $indent) . "]";
      return $code;
    case 'object':
      if (is_subclass_of($data, \zay\interfaces\StateInterface::class)) {
        $code = $data::class . "::__setState([\n";
        foreach ($data->__getState() as $key => $val) {
          $code .= sprintf("%s%s => %s,\n", str_repeat('  ', $indent + 1), json_encode_any($key), var_state($val, $indent + 1));
        }
        $code .= str_repeat('  ', $indent) . "])";
        return $code;
      } else {
        $code = "[\n";
        foreach ($data->__getState() as $key => $val) {
          $code .= sprintf("%s%s => %s,\n", str_repeat('  ', $indent + 1), json_encode_any($key), var_state($val, $indent + 1));
        }
        $code .= str_repeat('  ', $indent) . "]";
        return $code;
      }
    case 'NULL':
      return 'null';
    case 'resource':
      return "'resource'";
    case 'resource (closed)':
      return "'resource (closed)'";
    case 'unknown type':
      return "'unknown type'";
    default:
      throw new LogicException('unreachable');
  }
}

function app() : \zay\App {
  return \zay\App::getInstance();
}

function pagination() : string {
  return 'pagination';
}