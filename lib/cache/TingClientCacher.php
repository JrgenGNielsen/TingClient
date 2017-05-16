<?php

/**
 * @file Class TingClientCacher
 * Default implementation - override in extending methods
 */
class TingClientCacher implements \TingClientCacherInterface {

  /**
   * @var array
   */
  private static $cache = array();

  /**
   * Get cached value
   *
   * @param string $key
   *
   * @return bool
   */
  function get($key) {
    return isset(self::$cache[$key]) ? self::$cache[$key] : FALSE;
  }

  /**
   * Set cache
   *
   * @param string $key
   * @param mixed $value
   */
  function set($key, $value) {
    // use a standard object. If needed set timeout and other stuff on data object
    $data = new \stdClass();
    $data->data = $value;
    self::$cache[$key] = $data;
  }

  /**
   * Clear cache
   */
  function clear() {
    self::$cache = array();
  }
}