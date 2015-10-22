<?php

class TestCustomCacher implements TingClientCacherInterface {
  private static $cache = array();

  function set($key, $value, $storage = NULL, $expire = NULL) {
    self::$cache[$key] = $value;
  }

  function get($key) {
    return isset(self::$cache[$key]) ? self::$cache[$key] : FALSE;
  }

  function clear() {
    self::$cache = array();
  }
}




