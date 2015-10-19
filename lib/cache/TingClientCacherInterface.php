<?php

/**
 * @file
 * Interface TingClientCacherInterface
 * Caching interface
 */
interface TingClientCacherInterface{
  /**
   * Set the cache
   * @param $key
   * @param $value
   * @return mixed
   */
  function set($key, $value);

  /**
   * Get the cache
   * @param $key
   * @return mixed
   */
  function get($key);

  /**
   * Clear the cache
   * @return mixed
   */
  function clear();
}