<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

if (!function_exists('str_starts_with')) { // php8
  function str_starts_with(string $haystack, string $needle) : bool {
    return strpos($haystack, $needle) === 0;
  }

  function str_contains(string $haystack, string $needle) : bool {
    return strpos($haystack, $needle) !== false;
  }

  function str_ends_with(string $haystack, string $needle) : bool {
    return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
  }
}

if (!function_exists('array_is_list')) { // php8.1
  function array_is_list(array $array) : bool {
    for ($l = count($array), $i = 0; $i < $l; $i++) {
      if (!array_key_exists($i, $array)) { return false; }
    }
    return true;
  }
}

function app() : \zay\App {
  return \zay\App::getInstance();
}

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

function html(string $html) : string {
  return $html;
}

function env(string $name, mixed $defval = null) : mixed {
  return $_ENV[$name] ?? $defval;
}

function envbool(string $name, bool $defval = false) : bool {
  return boolval($_ENV[$name] ?? $defval);
}

function envstr(string $name, string $defval = '') : string {
  return strval($_ENV[$name] ?? $defval);
}

function envint(string $name, int $defval = 0) : int {
  return intval($_ENV[$name] ?? $defval);
}

function explode2(string $separator, ?string $string, int $limit = PHP_INT_MAX) : array {
  return empty($string) ? [] : explode($separator, $string, $limit);
}

function explode2int(string $separator, ?string $string) : array {
  return empty($string) ? [] : array_map('intval', explode($separator, $string));
}

function explode2float(string $separator, ?string $string) : array {
  return empty($string) ? [] : array_map('floatval', explode($separator, $string));
}

function implode2(string $separator, array $array) : string {
  return empty($array) ? '' : implode($separator, $array);
}

function json_encode_any(mixed $value) : string {
  return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function json_decode_any(?string $value) : mixed {
  return json_decode($value ?? '', true);
}

function json_encode_array(mixed $value) : string {
  return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function json_decode_array(?string $value) : mixed {
  return json_decode($value ?? '', true);
}

function json_encode_object(mixed $value) : string {
  return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | (is_array($value) && empty($value) ? JSON_FORCE_OBJECT : 0));
}

function json_decode_mixed(?string $value) : mixed {
  return json_decode($value ?? '', true);
}

// 日志参数
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

// 日志
function log_debug(mixed ...$args) : void {
  file_put_contents(mkdir2(APP_LOG . date('Ymd') . '.log'), date('Y-m-d H:i:s ') . implode(' ', log_args($args)) . "\n", FILE_APPEND);
}

// 日志
function log_debugf(string $format, mixed ...$args) : void {
  log_debug(sprintf($format, ...log_args($args)));
}

// 日志
function log_debug_return(mixed $retval, mixed ...$args) : mixed {
  log_debug(...$args); return $retval;
}

// 日志
function log_debugf_return(mixed $retval, string $format, mixed ...$args) : mixed {
  log_debugf($format, ...$args); return $retval;
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

// 创建目录
function mkdir2(string $filename) : string {
  $dirname = dirname($filename);
  if (!file_exists($dirname)) {
    mkdir($dirname, 0755, true);
  }
  return $filename;
}

// 随机字符串
function str_rand(int $len = 12, int $slen = 0, string $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', string $schars='~!@#$%^&*()_+`{}|[]\\:";\'<>,.?/') : string {
  if ($len < $slen) { throw new \UnexpectedValueException('str_rand: len < slen'); }
  $strs = [];
  for ($chars = str_split($chars), $charsLen = count($chars), $schars = str_split($schars), $scharsLen = count($schars), $i = 0; $i < $len; $i++) {
    $strs[] = $chars[random_int(0, $charsLen - 1)];
  }
  for ($sj = [], $j = 0, $i = 0; $i < $slen; $i++) {
    do {
      $j = random_int() % $len;
    } while (array_key_exists($j, $sj));
    $sj[$j] = true;
    $strs[$j] = $schars[random_int(0, $scharsLen - 1)];
  }
  return implode('', $strs);
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

function fileOriginTail(string $filename) : string {
  return '.' . substr(md5(filebase($filename) . APP_SECRET), 0, 10) . '.' . fileext($filename);
}

function fileOrigin(string $filename) : string {
  return $filename . fileOriginTail($filename);
}

function fileThumbTail(string $filename, string $name = 'default') : string {
  return $name === 'default' ? '' : '.' . $name . '.' . fileext($filename);
}

function fileThumb(string $filename, string $name = 'default') : string {
  return $filename . fileThumbTail($filename, $name);
}

function uploadUrl(string $filename) : string {
  $filename = filename($filename);
  return '/' . substr($filename, 0, 8) . '/' . $filename;
}

function uploadOriginUrl(string $filename) : string {
  $filename = filename($filename);
  return '/' . substr($filename, 0, 8) . '/' . fileOrigin($filename);
}

function uploadUnique(string $ext = 'jpg') {
  while (true) {
    $filename = date('YmdHis') . str_rand(10) . '.' . $ext;
    $filenameOrigin = fileOrigin($filename);
    if(file_exists(APP_UPLOAD . uploadUrl($filenameOrigin))) { continue; }
    return [$filename, $filenameOrigin];
  }
}

function editQuick(\zay\Request $req, \zay\Response $res, string $modelClass, string $rules, string $message = '编辑成功!') : void {
  $req->allowMethods('POST');
  $json = array_intersect_key($req->jsonVerify("id编号i,$rules"), $req->json);
  $data = $modelClass::find($json['id']);
  if (empty($data)) { $res->error('数据不存在!', [], 2); }
  $model = $modelClass::fromArray($json);
  $model->save();
  $res->success($message, [], 2);
}

function userEditQuick(\zay\Request $req, \zay\Response $res, string $modelClass, string $rules, string $message = '编辑成功!') : void {
  $req->allowMethods('POST');
  $json = array_intersect_key($req->jsonVerify("id编号i,$rules"), $req->json);
  $data = $req->user->callFind($modelName, $json['id']); // $modelClass::newSql()->whereByPk($json['id'])->where('`userId` = ?', $req->user->id)->select();
  if (empty($data)) { $res->error('数据不存在!', [], 2); }
  $model = $modelClass::fromArray($json);
  $model->save();
  $res->success($message, [], 2);
}

function uploadImage2(array $file, array ...$thumbs) : array {
  if ($file['error'] !== 0) {
    $errors = ['OK', '文件大小超过了'.ini_get('upload_max_filesize').'限制!', '文件大小超过了限制!', '文件上传不完整!', '没有文件被上传!', '找不到临时文件夹!', '文件写入失败!', '上传被扩展打断!'];
    return ['errno' => 1, 'errmsg' => $errors[$file['error']]];
  }
  [$width, $height, $type] = getimagesize($file['tmp_name']);
  if (!in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
    return ['errno' => 1, 'errmsg' => '格式错误!'];
  }
  $fileext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_JPEG2000 => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$type];
  [$filename, $filenameOrigin] = uploadUnique($fileext);
  $url = uploadUrl($filename);
  $urlOrigin = uploadUrl($filenameOrigin);
  $path = mkdir2(APP_UPLOAD . $url);
  $pathOrigin = mkdir2(APP_UPLOAD . $urlOrigin);
  move_uploaded_file($file['tmp_name'], $pathOrigin);
  $thumbs = !empty($thumbs) ? $thumbs : [['width' => 200, 'height' => 200]];
  foreach ($thumbs as $i => $thumb) {
    $thumb['name'] = $i === 0 ? 'default' : (!empty($thumb['name']) ? $thumb['name'] : "{$thumb['width']}x{$thumb['height']}");
    $thumb['in'] = isset($thumb['in']) ? $thumb['in'] : false;
    file_put_contents(fileThumb($path, $thumb['name']), thumbnail($pathOrigin, $thumb['width'], $thumb['height'], $thumb['in'])); // 缩略图
  }
  $errno = 0;
  $errmsg = 'ok';
  return compact('errno', 'errmsg', 'filename', 'fileext', 'width', 'height', 'url', 'urlOrigin', 'path', 'pathOrigin');
}

function uploadImage(\zay\Request $req, \zay\Response $res, array ...$thumbs) : void {
  if (!($req->method === 'POST' && !empty($req->get['action']) && $req->get['action'] === 'upload' && !empty($req->files['file']))) { return; }
  extract(uploadImage2($req->files['file'], ...$thumbs));
  if ($errno === 0) {
    $res->success('上传成功!', ['url' => $url, 'urlOrigin' => $urlOrigin]);
  } else {
    $res->error($errmsg);
  }
}

function uploadVideo2(array $file, array $args = []) : array {
  if ($file['error'] !== 0) {
    $errors = ['OK', '文件大小超过了'.ini_get('upload_max_filesize').'限制!', '文件大小超过了限制!', '文件上传不完整!', '没有文件被上传!', '找不到临时文件夹!', '文件写入失败!', '上传被扩展打断!'];
    return ['errno' => 1, 'errmsg' => $errors[$file['error']]];
  }
  $fileext = fileext($file['name']);
  if (!in_array($fileext, ['mp4'])) {
    return ['errno' => 1, 'errmsg' => '格式错误!'];
  }
  [$filename, $filenameOrigin] = uploadUnique($fileext);
  $url = uploadUrl($filename);
  $path = mkdir2(APP_UPLOAD . $url);
  move_uploaded_file($file['tmp_name'], $path);
  // 追加文件
  if (!empty($args['append'])) {
    $appendFilename = filename($args['append']);
    $appendUrl = uploadUrl($appendFilename);
    $appendPath = mkdir2(APP_UPLOAD . $appendUrl);
    file_put_contents($appendPath, file_get_contents($path), FILE_APPEND);
    unlink($path);
    $url = $appendUrl;
  }
  $errno = 0;
  $errmsg = 'ok';
  return compact('errno', 'errmsg', 'filename', 'fileext', 'url');
}

function uploadVideo(\zay\Request $req, \zay\Response $res) : void {
  if (!($req->method === 'POST' && !empty($req->get['action']) && $req->get['action'] === 'upload' && !empty($req->files['file']))) { return; }
  extract(uploadVideo2($req->files['file'], $req->post));
  if ($errno === 0) {
    $res->success('上传成功!', ['url' => $url]);
  } else {
    $res->error($errmsg);
  }
}

function uploadFile2(array $file) : array {
  if ($file['error'] !== 0) {
    $errors = ['OK', '文件大小超过了'.ini_get('upload_max_filesize').'限制!', '文件大小超过了限制!', '文件上传不完整!', '没有文件被上传!', '找不到临时文件夹!', '文件写入失败!', '上传被扩展打断!'];
    return ['errno' => 1, 'errmsg' => $errors[$file['error']]];
  }
  $fileext = fileext($file['name']);
  if (!in_array($fileext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'avi', 'mp4', 'wmv', 'flv'])) {
    return ['errno' => 1, 'errmsg' => '格式错误!'];
  }
  [$filename, $filenameOrigin] = uploadUnique($fileext);
  $url = uploadUrl($filename);
  $path = mkdir2(APP_UPLOAD . $url);
  move_uploaded_file($file['tmp_name'], $path);
  // 追加文件
  if (!empty($args['append'])) {
    $appendFilename = filename($args['append']);
    $appendUrl = uploadUrl($appendFilename);
    $appendPath = mkdir2(APP_UPLOAD . $appendUrl);
    file_put_contents($appendPath, file_get_contents($path), FILE_APPEND);
    unlink($path);
    $url = $appendUrl;
  }
  $errno = 0;
  $errmsg = 'ok';
  return compact('errno', 'errmsg', 'filename', 'fileext', 'url', 'path');
}

function uploadFile(\zay\Request $req, \zay\Response $res) : void {
  if (!($req->method === 'POST' && !empty($req->get['action']) && $req->get['action'] === 'upload' && !empty($req->files['file']))) { return; }
  extract(uploadFile2($req->files['file']));
  if ($errno === 0) {
    $res->success('上传成功!', ['url' => $url]);
  } else {
    $res->error($errmsg);
  }
}

function thumbnail(string $file, int $new_w = 200, $new_h = 200, bool $in = false) : string {
  if (!file_exists($file)) { throw new \LogicException('图片不存在!'); }
  $imgcreatefunc = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_JPEG2000 => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
  $imgsavefunc = [IMAGETYPE_JPEG => 'imagejpeg', IMAGETYPE_JPEG2000 => 'imagejpeg', IMAGETYPE_PNG => 'imagepng', IMAGETYPE_WEBP => 'imagewebp'];
  list($src_w, $src_h, $src_type) = getimagesize($file);
  if (!array_key_exists($src_type, $imgcreatefunc)) { throw new \LogicException('图片的类型不支持!'); }
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
  if (!file_exists($file)) { throw new \LogicException('图片不存在!'); }
  $imgcreatefunc = [IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_JPEG2000 => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng', IMAGETYPE_WEBP => 'imagecreatefromwebp'];
  $imgsavefunc = [IMAGETYPE_JPEG => 'imagejpeg', IMAGETYPE_JPEG2000 => 'imagejpeg', IMAGETYPE_PNG => 'imagepng', IMAGETYPE_WEBP => 'imagewebp'];
  list($src_w, $src_h, $src_type) = getimagesize($file);
  if (!array_key_exists($src_type, $imgcreatefunc)) { throw new \LogicException('图片的类型不支持!'); }
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

function setting_get(string $name, mixed $defval = null) : mixed {
  return \app\models\SystemSetting::whereByKey($name)->select()?->content ?? $defval;
}

function opcache_clear_cache(string $file) : string {
  if (function_exists('opcache_is_script_cached') && opcache_is_script_cached($file)) { opcache_invalidate($file, true); }
  return $file;
}

function data_action(string $action = '', mixed $name = '', mixed $val = null, string $code = '') : mixed {
  static $caches = [];
  if (APP_SWOOLE) {
    switch($action) {
      case 'set': return $caches[$name] = $val;
      case 'get': return $caches[$name] ?? $val;
      case 'del': unset($caches[$name]); return null;
      case 'has': return array_key_exists($name, $caches);
      default: return null;
    }
  } else {
    $file = APP_DATA . $name . '.php';
    switch($action) {
      case 'set': $caches[$name] = $val; file_put_contents(opcache_clear_cache(mkdir2($file)), $code ?: "<?php\nreturn ".var_state($val).";\n"); return $val;
      case 'get': return array_key_exists($name, $caches) ? $caches[$name] : (file_exists($file) ? $caches[$name] = require($file) : $val);
      case 'del': unset($caches[$name]); file_exists($file) && unlink(opcache_clear_cache($file)); return null;
      case 'has': return array_key_exists($name, $caches) ? true : (file_exists($file) ? true : false);
      default: return null;
    }
  }
}

function data_set(string $name, mixed $val = null, string $code = '') : mixed {
  return data_action('set', $name, $val, $code);
}

function data_get(string $name, mixed $defval = null) : mixed {
  return data_action('get', $name, $defval);
}

function data_del(string $name) : mixed {
  return data_action('del', $name);
}

function data_has(string $name) : bool {
  return data_action('has', $name);
}

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
      throw new \LogicException('unreachable');
  }
}
