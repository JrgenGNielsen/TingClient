<?php

/**
 * @file Class TingClientCacher
 * Default implementation - override in extending methods
 */
class TingClientCacher implements ITingClientCacherInterface{

  private static $cache = array();
  public function __construct(){}

  function get($key) {
    return isset(self::$cache[$key]) ? self::$cache[$key] : FALSE;
  }

  function set($key, $value) {
    self::$cache[$key] = $value;
  }

  function clear(){
    self::$cache = array();
  }
}