<?php
/* Copyright 2021 The dotcoo <dotcoo@163.com>. All rights reserved. */

declare(strict_types=1);

namespace net\phpm\framework;

use net\phpm\framework\exceptions\VerifyFailException;
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
  private function advance() : static {
    $this->current = ++$this->pos < $this->len ? mb_substr($this->text, $this->pos, 1) : '';
    return $this;
  }
  private function skipWhitespace() : static {
    while ($this->current !== '' && ctype_space($this->current)) {
      $this->advance();
    }
    return $this;
  }
  private function readLParentheses() : void {
    if ($this->current === '(') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 [ 找到了 {$this->current}");
    }
  }
  private function readRParentheses() : void {
    if ($this->current === ')') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 ] 找到了 {$this->current}");
    }
  }
  private function readLBrackets() : void {
    if ($this->current === '[') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到[ 找到了{$this->current}");
    }
  }
  private function readRBrackets() : void {
    if ($this->current === ']') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到] 找到了{$this->current}");
    }
  }
  private function readLBraces() : void {
    if ($this->current === '{') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到{ 找到了{$this->current}");
    }
  }
  private function readRBraces() : void {
    if ($this->current === '}') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到} 找到了{$this->current}");
    }
  }
  private function isDoubleQuotes() : bool {
    return $this->current === '"';
  }
  private function readDoubleQuotes() : void {
    if ($this->current === '"') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 \" 找到了 {$this->current}");
    }
  }
  private function isSingleQuotes() : bool {
    return $this->current === "'";
  }
  private function readSingleQuotes() : void {
    if ($this->current === "'") {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 ' 找到了 {$this->current}");
    }
  }
  private function isComma() : bool {
    return $this->current === ',';
  }
  private function readComma() : void {
    if ($this->current === ',') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 , 找到了 {$this->current}");
    }
  }
  private function isSlash() : bool {
    return $this->current === '/';
  }
  private function readSlash() : void {
    if ($this->current === '/') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 / 找到了 {$this->current}");
    }
  }
  private function readVertical() : void {
    if ($this->current === '|') {
      $this->advance()->skipWhitespace();
    } else {
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 没有找到 | 找到了 {$this->current}");
    }
  }
  private function isDot() : bool {
    return $this->current === '.';
  }
  private function readDot() : void {
    if ($this->current === '.') {
      $this->advance()->skipWhitespace();
    }
  }
  private function isEnd() : bool {
    return $this->current !== '' && preg_match('/^(\.|,|\/|\(|\)|\[|\]|\{|\}|\s)$/', $this->current);
  }
  private function isNode() : bool {
    return in_array($this->current, ['b', 'B', 'i', 'I', 'u', 'U', 'y', 'Y', 'f', 'F', 'g', 'G', 'h', 'H', 's', 'S', 'a', 'A', 'o', 'O']);
  }
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
    } else if ($this->current === 'm') {
      $node = [ 'type' => 'mixed', 'required' => true ];
    } else if ($this->current === 'M') {
      $node = [ 'type' => 'mixed', 'required' => false ];
    } else {
      return [ 'type' => 'string', 'required' => true ];
    }
    $this->advance()->skipWhitespace();
    $this->readDot();
    return $node;
  }
  private function isDefaultBegin() : bool {
    return $this->current === '(';
  }
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
  private function isNumberBegin() : bool {
    return ctype_digit($this->current) || $this->current === '-';
  }
  private function isNumber() : bool {
    return ctype_digit($this->current);
  }
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
  private function isFloat() : bool {
    return ctype_digit($this->current);
  }
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
  private function isRangeBegin() : bool {
    return $this->isNumber();
  }
  private function isRangeEnd() : bool {
    return $this->current !== '' && $this->current === ')';
  }
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
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 最大值不能小于最小值!");
    }
    $this->readDot();
    return [$min, $max];
  }
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
      throw new VerifyFailException("rules: {$this->text}, pos: {$this->pos}, 最大值不能小于最小值!");
    }
    $this->readDot();
    return [$min, $max];
  }
  private function isRegexpBegin() : bool {
    return $this->current === '/';
  }
  private function isRegexpEnd() : bool {
    return $this->current !== '' && $this->current === '/';
  }
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
  private function isRegexpMessageBegin() : bool {
    return $this->current === '|';
  }
  private function isRegexpMessageEnd() : bool {
    return $this->current !== '' && $this->current === '|';
  }
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
  private function isKey() : bool {
    return ctype_alnum($this->current) || $this->current === '_';
  }
  private function readKey() : string {
    $result = '';
    while ($this->isKey()) {
      $result .= $this->current;
      $this->advance();
    }
    return $result;
  }
  private function isLabel() : bool {
    return $this->isQuotes() || $this->isZH() || $this->isWord();
  }
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
    throw new VerifyFailException('unreachable!');
  }
  private function isQuotes() : bool {
    return $this->current === '"' || $this->current === "'";
  }
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
  private function isZH() : bool {
    return $this->current !== '' && strlen(urlencode($this->current)) >= 9;
  }
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
  private function isWord() : bool {
    return $this->current !== '' && ctype_alnum($this->current);
  }
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
    $node = $this->readType();
    $this->skipWhitespace();
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
      $node['defval'] = $this->readDefaultValue();
    }

    if ($node['type'] === 'int') {
      $node['defval'] = $this->readDefaultValue();
      [$node['min'], $node['max']] = $this->readNumberRange();
    }

    if ($node['type'] === 'float') {
      $node['defval'] = $this->readDefaultValue();
      [$node['min'], $node['max']] = $this->readFloatRange();
    }

    if ($node['type'] === 'string') {
      $node['defval'] = $this->readDefaultValue();
      [$node['min'], $node['max']] = $node['type'] === 'float' ? $this->readFloatRange() : $this->readNumberRange();
      $node['regexp'] = $this->readRegexp() ?: null;
      $node['regexpErrmsg'] = empty($node['regexp']) ? '' : ($this->readRegexpMessage() ?: null);
    }

    if ($node['type'] === 'array') {
      [$node['min'], $node['max']] = $node['type'] === 'float' ? $this->readFloatRange() : $this->readNumberRange();

      $this->readLBrackets();
      $node['item'] = $this->readNode($node);
      $this->readRBrackets();
    }

    if ($node['type'] === 'object') {
      $this->readLBraces();
      while ($this->isKey()) {
        $key = $this->readKey();
        $label = $this->isLabel() ? $this->readLabel() : $key;
        $fieldNode = $this->readNode($node);
        $fieldNode['key'] = $key;
        $fieldNode['label'] = $label;
        array_push($node['fields'], $fieldNode);
        if ($this->isComma()) {
          $this->readComma();
        } else {
          break;
        }
      }
      $this->readRBraces();
    }

    return $node;
  }
  private function parse() : array {
    return $this->readNode(null);
  }
  public static array $regexps = [];
  public static array $rulesCache = [];
  public static function parseRules(string $rules) : array {
    if (!array_key_exists($rules, static::$rulesCache)) {
      $paramsRules = new static($rules);
      static::$rulesCache[$rules] = $paramsRules->parse();
    }
    return static::$rulesCache[$rules];
  }
  public static function paramsVerify(mixed $val, string $rules, string $fullpath = '', string $label = '', int $depth = 0) : array {
    return static::dataVerify($val, static::parseRules($rules), $fullpath, $label, $depth + 1);
  }
  public static function dataVerify(mixed $val, array $rules, string $fullpath = '', string $label = '', int $depth = 0) : mixed {
    if (is_string($val)) { $val = trim($val); }
    $isEmpty = fn(mixed $val) : bool => $val === null || $val === '' || $val === 'null' || $val === 'NULL';
    $r = $rules;
    $path = substr($fullpath, 1);
    if ($r['type'] === 'bool') {
      if ($r['defval'] !== '' && $isEmpty($val)) {
        $val = $r['defval'];
      }
      if (!$r['required'] && $isEmpty($val)) {
        return false;
      }
      return $val === true || $val === 'true' || $val === 1 || $val === '1';
    }
    if ($r['type'] === 'int' || $r['type'] === 'float') {
      if ($r['defval'] !== '' && $isEmpty($val)) {
        $val = $r['defval'];
      }
      if (!$r['required'] && $isEmpty($val)) {
        return 0;
      }
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyFailException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      if (is_string($val) && is_numeric($val)) {
        $val = $val - 0;
      }
      if (is_bool($val)) {
        $val = $val ? 1 : 0;
      }
      if ($r['type'] === 'int' && !is_int($val) || $r['type'] === 'float' && !(is_int($val) || is_float($val))) {
        throw new VerifyFailException("{$label}必须是一个数字!", 1, $depth + 1, $path);
      }
      if (!$r['minus'] && $val < 0) {
        throw new VerifyFailException("{$label}不能为负数!", 1, $depth + 1, $path);
      }
      if (!$r['zero'] && $val === 0) {
        throw new VerifyFailException("{$label}不能为0!", 1, $depth + 1, $path);
      }
      if ($r['min'] !== null && $val < $r['min']) {
        throw new VerifyFailException("{$label}最小为{$r['min']}!", 1, $depth + 1, $path);
      }
      if ($r['max'] !== null && $val > $r['max']) {
        throw new VerifyFailException("{$label}最大为{$r['max']}!", 1, $depth + 1, $path);
      }
      return $val;
    }
    if ($r['type'] === 'string') {
      if ($r['defval'] !== '' && $isEmpty($val)) {
        $val = $r['defval'];
      }
      if (!$r['required'] && $isEmpty($val)) {
        return '';
      }
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyFailException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      if (is_bool($val)) {
        $val = $val ? '1' : '0';
      }
      if (is_null($val) || is_float($val)) {
        $val = $val + '';
      }
      if (!is_string($val)) {
        throw new VerifyFailException("{$label}必须是一个字符串!", 1, $depth + 1, $path);
      }
      if ($r['min'] !== null && mb_strlen($val) < $r['min'] || $r['max'] !== null && mb_strlen($val) > $r['max']) {
        throw new VerifyFailException("{$label}长度为{$r['min']}-{$r['max']}位!", 1, $depth + 1, $path);
      }
      if ($r['regexp'] !== null && !preg_match($r['regexp'], $val)) {
        throw new VerifyFailException(empty($r['regexpErrmsg']) ? "{$label}格式不正确!" : $r['regexpErrmsg'], 1, $depth + 1, $path);
      }
      return $val;
    }
    if ($r['type'] === 'array') {
      if (!$r['required'] && $isEmpty($val)) {
        return [];
      }
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyFailException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      if (!is_array($val)) {
        throw new VerifyFailException("{$label}必须是一个数组!", 1, $depth + 1, $path);
      }
      if ($r['required'] && count($val) === 0) {
        throw new VerifyFailException("{$label}不能为空数组!", 1, $depth + 1, $path);
      }
      if ($r['min'] !== null && count($val) < $r['min']) {
        throw new VerifyFailException("{$label}数量最少{$r['min']}个!", 1, $depth + 1, $path);
      }
      if ($r['max'] !== null && count($val) > $r['max']) {
        throw new VerifyFailException("{$label}数量最多{$r['max']}个!", 1, $depth + 1, $path);
      }
      $newVal = [];
      foreach ($val as $i => $item) {
        $no = $i + 1;
        array_push($newVal, static::dataVerify($item, $r['item'], "{$fullpath}[{$i}]", "{$label}{$no}", $depth + 1));
      }
      return $newVal;
    }
    if ($r['type'] === 'object') {
      if (!$r['required'] && $isEmpty($val)) {
        return [];
      }
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyFailException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      if (!is_array($val)) {
        throw new VerifyFailException("{$label}必须是一个对象!", 1, $depth + 1, $path);
      }
      $newVal = [];
      foreach ($r['fields'] as $col) {
        $newVal[$col['key']] = static::dataVerify($val[$col['key']] ?? null, $col, "{$fullpath}.{$col['key']}", "{$label}{$col['label']}", $depth + 1);
      }
      return $newVal;
    }
    if ($r['type'] === 'mixed') {
      if (!$r['required'] && $isEmpty($val)) {
        return null;
      }
      if ($r['required'] && $isEmpty($val)) {
        throw new VerifyFailException("{$label}不能为空!", 1, $depth + 1, $path);
      }
      return $val;
    }
  }
}
