<?php
// Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved.

declare(strict_types=1);

namespace zay;

use zay\exceptions\VerifyException;

// o{username用户名.s8-16/^[a-zA-Z]\\w{7-15}$/"请输入用户名!"}
// o{ 键 中文名称.(点分隔符) 类型(小写必填 大写选填) 最小值(可省略)-(可省略)最大值 (默认值) /xxx/(正则表达式 不支持flags) |错误提示| }

// 参数规则
final class Verify {

  private string $text = '';

  private int $len = 0;

  private int $pos = -1;

  private string $current = '';

  private function __construct(string $text) {
    $this->text = $text;
    $this->len = mb_strlen($text);
    $this->advance();
  }

  // 前进
  private function advance() : static {
    $this->current = ++$this->pos < $this->len ? mb_substr($this->text, $this->pos, 1) : '';
    return $this;
  }

  // 跳过空白
  private function skipWhitespace() : static {
    while ($this->current !== '' && ctype_space($this->current)) {
      $this->advance();
    }
    return $this;
  }

  // 左小括号
  private function readLParentheses() : void {
    if ($this->current === '(') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 [ 找到了 {$this->current}");
    }
  }

  // 右小括号
  private function readRParentheses() : void {
    if ($this->current === ')') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 ] 找到了 {$this->current}");
    }
  }

  // 左中括号
  private function readLBrackets() : void {
    if ($this->current === '[') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到[ 找到了{$this->current}");
    }
  }

  // 右中括号
  private function readRBrackets() : void {
    if ($this->current === ']') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到] 找到了{$this->current}");
    }
  }

  // 左大括号
  private function readLBraces() : void {
    if ($this->current === '{') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到{ 找到了{$this->current}");
    }
  }

  // 右大括号
  private function readRBraces() : void {
    if ($this->current === '}') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到} 找到了{$this->current}");
    }
  }

  // 是否双引号
  private function isDoubleQuotes() : bool {
    return $this->current === '"';
  }

  // 双引号
  private function readDoubleQuotes() : void {
    if ($this->current === '"') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 \" 找到了 {$this->current}");
    }
  }

  // 是否单引号
  private function isSingleQuotes() : bool {
    return $this->current === "'";
  }

  // 单引号
  private function readSingleQuotes() : void {
    if ($this->current === "'") {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 ' 找到了 {$this->current}");
    }
  }

  // 是否逗号
  private function isComma() : bool {
    return $this->current === ',';
  }

  // 逗号
  private function readComma() : void {
    if ($this->current === ',') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 , 找到了 {$this->current}");
    }
  }

  // 是否斜杠
  private function isSlash() : bool {
    return $this->current === '/';
  }

  // 斜杠
  private function readSlash() : void {
    if ($this->current === '/') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 / 找到了 {$this->current}");
    }
  }

  // 竖线
  private function readVertical() : void {
    if ($this->current === '|') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 没有找到 | 找到了 {$this->current}");
    }
  }

  // 是否点号
  private function isDot() : bool {
    return $this->current === '.';
  }

  // 点号
  private function readDot() : void {
    if ($this->current === '.') {
      $this->advance()->skipWhitespace();
    }
  }

  // 结尾
  private function isEnd() : bool {
    return $this->current !== '' && parg_match('/^(\.|,|\/|\(|\)|\[|\]|\{|\}|\s)$/', $this->current);
  }

  // 是否节点
  private function isNode() : bool {
    return in_array($this->current, ['b', 'B', 'i', 'I', 'u', 'U', 'y', 'Y', 'f', 'F', 'g', 'G', 'h', 'H', 's', 'S', 'a', 'A', 'o', 'O']);
  }

  // 类型
  private function readType() : array {
    $node = null;
    if ($this->current === 'b') {
      $node = [ 'type' => 'bool', 'required' => true ];
    } else if ($this->current === 'B') {
      $node = [ 'type' => 'bool', 'required' => false ];
    } else if ($this->current === 'i') {
      $node = [ 'type' => 'int', 'required' => true, 'zero' => false, 'minus' => false ];
    } else if ($this->current === 'I') {
      $node = [ 'type' => 'int', 'required' => false, 'zero' => false, 'minus' => false ];
    } else if ($this->current === 'u') {
      $node = [ 'type' => 'int', 'required' => true, 'zero' => true, 'minus' => false ];
    } else if ($this->current === 'U') {
      $node = [ 'type' => 'int', 'required' => false, 'zero' => true, 'minus' => false ];
    } else if ($this->current === 'y') {
      $node = [ 'type' => 'int', 'required' => true, 'zero' => true, 'minus' => true ];
    } else if ($this->current === 'Y') {
      $node = [ 'type' => 'int', 'required' => false, 'zero' => true, 'minus' => true ];
    } else if ($this->current === 'f') {
      $node = [ 'type' => 'float', 'required' => true, 'zero' => false, 'minus' => false ];
    } else if ($this->current === 'F') {
      $node = [ 'type' => 'float', 'required' => false, 'zero' => false, 'minus' => false ];
    } else if ($this->current === 'g') {
      $node = [ 'type' => 'float', 'required' => true, 'zero' => true, 'minus' => false ];
    } else if ($this->current === 'G') {
      $node = [ 'type' => 'float', 'required' => false, 'zero' => true, 'minus' => false ];
    } else if ($this->current === 'h') {
      $node = [ 'type' => 'float', 'required' => true, 'zero' => true, 'minus' => true ];
    } else if ($this->current === 'H') {
      $node = [ 'type' => 'float', 'required' => false, 'zero' => true, 'minus' => true ];
    } else if ($this->current === 's') {
      $node = [ 'type' => 'string', 'required' => true ];
    } else if ($this->current === 'S') {
      $node = [ 'type' => 'string', 'required' => false ];
    } else if ($this->current === 'a') {
      $node = [ 'type' => 'array', 'required' => true ];
    } else if ($this->current === 'A') {
      $node = [ 'type' => 'array', 'required' => false ];
    } else if ($this->current === 'o') {
      $node = [ 'type' => 'object', 'required' => true ];
    } else if ($this->current === 'O') {
      $node = [ 'type' => 'object', 'required' => false ];
    } else {
      return [ 'type' => 'string', 'required' => true ];
    }
    $this->advance()->skipWhitespace();
    $this->readDot();
    return $node;
  }

  // 是否默认值
  private function isDefaultBegin() : bool {
    return $this->current === '(';
  }

  // 默认值
  private function readDefaultValue() : string {
    if (!$this->isDefaultBegin()) {
      return '';
    }
    $this->readLParentheses();
    $result = '';
    $expect = true;
    while ($this->current !== '') {
      if ($expect && $this->current === ')') {
        break;
      } else if ($expect && $this->current === '\\') {
        $expect = false;
      } else {
        $expect = true;
      }
      $result .= $this->current;
      $this->advance();
    }
    $this->readRParentheses();
    $this->skipWhitespace();
    $this->readDot();
    return $result;
  }

  // 是否数字开始
  private function isNumberBegin() : bool {
    return ctype_digit($this->current) || $this->current === '-';
  }

  // 是否数字
  private function isNumber() : bool {
    return ctype_digit($this->current);
  }

  // 数字
  private function readNumber() : int {
    $result = '';
    if ($this->current === '-') {
      $result .= $this->current;
      $this->advance();
    }
    while ($this->isNumber()) {
      $result .= $this->current;
      $this->advance();
    }
    $this->skipWhitespace();
    return intval($result);
  }

  // 是否float
  private function isFloat() : bool {
    return ctype_digit($this->current);
  }

  // float
  private function readFloat() : float {
    $result = '';
    if ($this->current === '-') {
      $result .= $this->current;
      $this->advance();
    }
    while ($this->isNumber()) {
      $result .= $this->current;
      $this->advance();
    }
    if (!$this->isDot()) {
      $this->skipWhitespace();
      return floatval($result);
    }
    $result .= $this->current;
    while ($this->isNumber()) {
      $result .= $this->current;
      $this->advance();
    }
    $this->skipWhitespace();
    return floatval($result);
  }

  // 是否范围开始
  private function isRangeBegin() : bool {
    return $this->isNumber();
  }

  // 是否范围结束
  private function isRangeEnd() : bool {
    return $this->current !== '' && $this->current === ')';
  }

  // 范围
  private function readNumberRange() : array {
    if (!$this->isRangeBegin()) {
      return [null, null];
    }
    $min = $this->readNumber();
    if ($this->current !== '-') {
      return [0, $min];
    }
    $this->advance()->skipWhitespace();
    $max = $this->readNumber();
    if ($max < $min) {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 最大值不能小于最小值!");
    }
    $this->readDot();
    return [$min, $max];
  }

  // float范围
  private function readFloatRange() : array {
    if (!$this->isRangeBegin()) {
      return [null, null];
    }
    $min = $this->readFloat();
    if ($this->current !== '-') {
      return [0, $min];
    }
    $this->advance()->skipWhitespace();;
    $max = $this->readFloat();
    if ($max < $min) {
      throw new VerifyException("rules: {$this->text}, pos: {$this->pos}, 最大值不能小于最小值!");
    }
    $this->readDot();
    return [$min, $max];
  }

  // 是否正则开始
  private function isRegexpBegin() : bool {
    return $this->current === '/';
  }

  // 是否正则开始
  private function isRegexpEnd() : bool {
    return $this->current !== '' && $this->current === '/';
  }

  // 正则
  private function readRegexp() : string {
    if (!$this->isRegexpBegin()) {
      return '';
    }
    $result = '';
    $expect = true;
    $this->readSlash();
    while ($this->current !== '') {
      if ($expect && $this->isRegexpEnd()) {
        break;
      } else if ($expect && $this->current === '\\') {
        $expect = false;
      } else {
        $expect = true;
      }
      $result .= $this->current;
      $this->advance();
    }
    $this->readSlash();
    $this->readDot();
    if (array_key_exists($result, static::$regexps)) {
      return static::$regexps[$result];
    }
    return $result;
  }

  // 是否消息开始
  private function isRegexpMessageBegin() : bool {
    return $this->current === '|';
  }

  // 是否消息开始
  private function isRegexpMessageEnd() : bool {
    return $this->current !== '' && $this->current === '|';
  }

  // 消息
  private function readRegexpMessage() : string {
    if (!$this->isRegexpMessageBegin()) {
      return '';
    }
    $result = '';
    $expect = true;
    $this->readVertical();
    while ($this->current !== '') {
      if ($expect && $this->isRegexpMessageEnd()) {
        break;
      } else if ($expect && $this->current === '\\') {
        $expect = false;
      } else {
        $expect = true;
      }
      $result .= $this->current;
      $this->advance();
    }
    $this->readVertical();
    $this->readDot();
    return $result;
  }

  // 是否是Key
  private function isKey() : bool {
    return ctype_alnum($this->current) || $this->current === '_';
  }

  // 读取Key
  private function readKey() : string {
    $result = '';
    while ($this->isKey()) {
      $result .= $this->current;
      $this->advance();
    }
    return $result;
  }

  // 是否标签
  private function isLabel() : bool {
    return $this->isQuotes() || $this->isZH() || $this->isWord();
  }

  // 标签
  private function readLabel() : string {
    if ($this->isQuotes()) {
      return $this->readLabelQuote($this->current);
    }
    if ($this->isZH()) {
      return $this->readLabelZH();
    }
    if ($this->isWord()) {
      return $this->readLabelAll();
    }
    throw new VerifyException('unreachable!');
  }

  // 是否引号标签
  private function isQuotes() : bool {
    return $this->current === '"' || $this->current === "'";
  }

  // 引号标签
  private function readLabelQuote(string $quote) : string {
    $result = '';
    $expect = true;
    $this->advance();
    while ($this->current !== '') {
      if ($expect && $this->current === $quote) {
        break;
      } else if ($expect && $this->current === '\\') {
        $expect = false;
      } else {
        $expect = true;
      }
      $result .= $this->current;
      $this->advance();
    }
    $this->advance();
    $this->skipWhitespace();
    $this->readDot();
    return $result;
  }

  // 是否中文标签
  private function isZH() : bool {
    return $this->current !== '' && strlen(urlencode($this->current)) >= 9;
  }

  // 中文标签
  private function readLabelZH() : string {
    $result = '';
    while ($this->isZH()) {
      $result .= $this->current;
      $this->advance();
    }
    $this->skipWhitespace();
    $this->readDot();
    return $result;
  }

  // 是否英文单词
  private function isWord() : bool {
    return $this->current !== '' && ctype_alnum($this->current);
  }

  // 英文标签
  private function readLabelAll() : string {
    $result = '';
    while (!$this->isEnd()) {
      $result .= $this->current;
      $this->advance();
    }
    $this->skipWhitespace();
    $this->readDot();
    return $result;
  }

  private function readNode($parent) : array {
    // 读取节点
    $node = $this->readType();
    $this->skipWhitespace();
    // $node['parent'] = $parent;
    $node['key'] = '';
    $node['label'] = '';
    $node['min'] = null;
    $node['max'] = null;
    $node['defval'] = null;
    $node['regexp'] = null;
    $node['regexpErrmsg'] = null;
    $node['item'] = null;
    $node['fields'] = [];

    if ($node['type'] === 'bool') {
      // 读取默认值
      $node['defval'] = $this->readDefaultValue();
    }

    if ($node['type'] === 'int') {
      // 读取默认值
      $node['defval'] = $this->readDefaultValue();

      // 读取range
      [$node['min'], $node['max']] = $this->readNumberRange();
    }

    if ($node['type'] === 'float') {
      // 读取默认值
      $node['defval'] = $this->readDefaultValue();

      // 读取range
      [$node['min'], $node['max']] = $this->readFloatRange();
    }

    if ($node['type'] === 'string') {
      // 读取默认值
      $node['defval'] = $this->readDefaultValue();

      // 读取range
      [$node['min'], $node['max']] = $node['type'] === 'float' ? $this->readFloatRange() : $this->readNumberRange();

      // 读取格式
      $node['regexp'] = $this->readRegexp() ?: null;

      // 读取格式错误提示
      $node['regexpErrmsg'] = empty($node['regexp']) ? '' : ($this->readRegexpMessage() ?: null);
    }

    if ($node['type'] === 'array') {
      // 读取range
      [$node['min'], $node['max']] = $node['type'] === 'float' ? $this->readFloatRange() : $this->readNumberRange();

      $this->readLBrackets(); // 左中括号
      $node['item'] = $this->readNode($node);
      $this->readRBrackets(); // 友中括号
    }

    if ($node['type'] === 'object') {
      $this->readLBraces(); // 左大括号
      while ($this->isKey()) {
        // 读取key
        $key = $this->readKey();
        // 读取label
        $label = $this->isLabel() ? $this->readLabel() : $key;
        // 读取节点类型
        $fieldNode = $this->readNode($node);
        $fieldNode['key'] = $key;
        $fieldNode['label'] = $label;
        $node['fields'][] = $fieldNode;
        if ($this->isComma()) {
          $this->readComma();
        } else {
          break;
        }
      }
      $this->readRBraces(); // 右大括号
    }

    return $node;
  }

  // 解析
  private function parse() : array {
    return $this->readNode(null);
  }

  // ====== 静态检测 ======

  // 正则表达式映射表
  protected static array $regexps = [];

  public static function setRegexps(array $regexps) : void {
    static::$regexps = $regexps;
  }

  public static function addRegexp(string $name, string $regexp) : void {
    static::$regexps[$name] = $regexp;
  }

  // 规则缓存
  protected static array $rulesCache = [];

  // 解析规则
  public static function parseRules(string $rules) : array {
    if (!array_key_exists($rules, self::$rulesCache)) {
      $paramsRules = new self($rules);
      self::$rulesCache[$rules] = $paramsRules->parse();
    }
    return self::$rulesCache[$rules];
  }

  // 参数验证
  public static function paramsVerify(mixed $val, string $rules, string $fullpath = '', string $label = '', int $depth = 0) : array {
    return self::dataVerify($val, self::parseRules("o{{$rules}}"), $fullpath, $label, $depth + 1);
  }
  
  // 检查数据
  public static function dataVerify(mixed $val, array $rules, string $fullpath = '', string $label = '', int $depth = 0) : mixed {
    // 两侧空格
    if (is_string($val)) { $val = trim($val); }

    // 是否为空
    $isEmpty = fn(mixed $val) : bool => $val === null || $val === '' || $val === 'null' || $val === 'NULL';

    // 变量
    $r = $rules;
    $path = substr($fullpath, 1);

    // boolean
    if ($r['type'] === 'bool') {
      // 默认值
      if ($r['defval'] !== '' && $isEmpty($val)) {
        $val = $r['defval'];
      }
      // 转换
      return $val === true || $val === 'true' || $val === 1 || $val === '1';
    }

    // 整数 小数
    if ($r['type'] === 'int' || $r['type'] === 'float') {
      // 默认值
      if ($r['defval'] !== '' && $isEmpty($val)) {
        $val = $r['defval'];
      }
      // 必填 没填
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      // 选填 没填
      if (!$r['required'] && $isEmpty($val)) {
        return 0;
      }
      // 兼容
      if (is_string($val) && is_numeric($val)) {
        $val = $val - 0;
      }
      if (is_bool($val)) {
        $val = $val ? 1 : 0;
      }
      // 类型
      if ($r['type'] === 'int' && !is_int($val) || $r['type'] === 'float' && !(is_int($val) || is_float($val))) {
        throw new VerifyException("{$label}必须是一个数字!", 1, $depth + 1, $path);
      }
      // 负数
      if (!$r['minus'] && $val < 0) {
        throw new VerifyException("{$label}不能为负数!", 1, $depth + 1, $path);
      }
      // 零
      if (!$r['zero'] && $val === 0) {
        throw new VerifyException("{$label}不能为0!", 1, $depth + 1, $path);
      }
      // 最小值
      if ($r['min'] !== null && $val < $r['min']) {
        throw new VerifyException("{$label}最小为{$r['min']}!", 1, $depth + 1, $path);
      }
      // 最大值
      if ($r['max'] !== null && $val > $r['max']) {
        throw new VerifyException("{$label}最大为{$r['max']}!", 1, $depth + 1, $path);
      }
      return $val;
    }

    // 字符串
    if ($r['type'] === 'string') {
      // 默认值
      if ($r['defval'] !== '' && $isEmpty($val)) {
        $val = $r['defval'];
      }
      // 必填 没填
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      // 选填 没填
      if (!$r['required'] && $isEmpty($val)) {
        return '';
      }
      // 兼容
      if (is_bool($val)) {
        $val = $val ? '1' : '0';
      }
      // 兼容
      if (is_null($val) || is_float($val)) {
        $val = $val + '';
      }
      // 类型
      if (!is_string($val)) {
        throw new VerifyException("{$label}必须是一个字符串!", 1, $depth + 1, $path);
      }
      // 长度范围
      if ($r['min'] !== null && mb_strlen($val) < $r['min'] || $r['max'] !== null && mb_strlen($val) > $r['max']) {
        throw new VerifyException("{$label}长度为{$r['min']}-{$r['max']}位!", 1, $depth + 1, $path);
      }
      // 正则表达式
      if ($r['regexp'] !== null && !preg_match($r['regexp'], $val)) {
        throw new VerifyException(empty($r['regexpErrmsg']) ? "{$label}格式不正确!" : $r['regexpErrmsg'], 1, $depth + 1, $path);
      }
      return $val;
    }

    // 数组
    if ($r['type'] === 'array') {
      // 必填 没填
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      // 类型
      if (!is_array($val)) {
        throw new VerifyException("{$label}必须是一个数组!", 1, $depth + 1, $path);
      }
      // 必填 空数组
      if ($r['required'] && count($val) === 0) {
        throw new VerifyException("{$label}不能为空数组!", 1, $depth + 1, $path);
      }
      // 选填 没填
      if (!$r['required'] && $isEmpty($val)) {
        return [];
      }
      // 最小
      if ($r['min'] !== null && count($val) < $r['min']) {
        throw new VerifyException("{$label}数量最少{$r['min']}个!", 1, $depth + 1, $path);
      }
      // 最大
      if ($r['max'] !== null && count($val) > $r['max']) {
        throw new VerifyException("{$label}数量最多{$r['max']}个!", 1, $depth + 1, $path);
      }
      // 数组元素
      $newVal = [];
      foreach ($val as $i => $item) {
        $no = $i + 1;
        $newVal[] = static::dataVerify($item, $r['item'], "{$fullpath}[{$i}]", "{$label}{$no}", $depth + 1);
      }
      return $newVal;
    }

    // 对象
    if ($r['type'] === 'object') {
      // 必填 没填
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      // 选填 没填
      if (!$r['required'] && $isEmpty($val)) {
        return [];
      }
      // 类型
      if (!is_array($val)) {
        throw new VerifyException("{$label}必须是一个对象!", 1, $depth + 1, $path);
      }
      // 对象元素
      $newVal = [];
      foreach ($r['fields'] as $col) {
        $newVal[$col['key']] = static::dataVerify($val[$col['key']] ?? null, $col, "{$fullpath}.{$col['key']}", "{$label}{$col['label']}", $depth + 1);
      }
      return $newVal;
    }
  }
}
