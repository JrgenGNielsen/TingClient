<?php

/**
 * @file Class TingClientCacher
 * Default implementation - override in extending methods
 */
class TingClientCacher implements TingClientCacherInterface {

  /**
   * @var array
   */
  private static $cache = array();

  /**
   * Get cached value
   *
   * @param $key
   *
   * @return bool
   */
  function get($key) {
    return isset(self::$cache[$key]) ? self::$cache[$key] : FALSE;
  }

  /**
   * Set cache
   *
   * @param $key
   * @param $value
   */
  function set($key, $value) {
    self::$cache[$key] = $value;
  }

  /**
   * Clear cache
   */
  function clear() {
    self::$cache = array();
  }
}