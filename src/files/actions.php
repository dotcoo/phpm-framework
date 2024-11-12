<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

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