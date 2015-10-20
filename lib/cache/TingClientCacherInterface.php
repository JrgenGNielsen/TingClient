<?php

/**
 * @file
 * Interface TingClientCacherInterface
 * Caching interface
 */
interface TingClientCacherInterface {
  /**
   * Set the cache
   *
   * @param string $key
   * @param string $value
   *
   */
  function set($key, $value);

  /**
   * Get the cache
   *
   * @param string $key
   *
   * @return mixed
   */
  function get($key);

  /**
   * Clear the cache
   *
   */
  function clear();
}