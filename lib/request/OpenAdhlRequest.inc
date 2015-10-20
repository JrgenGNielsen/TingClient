<?php

/**
 * @file
 * OpenAdhlRequest class
 **/
class OpenAdhlRequest extends TingClientRequest Implements TingClientRequestCacheInterface {

  /**
   * TingClientRequestCacheInterface::cacheKey; get a cachekey
   *
   * @return string
   **/
  public function cacheKey() {
    $params = $this->getParameters();
    $ret = '';
    $this->make_cache_key($params, $ret);

    return md5($ret);
  }

  /**
   * Make a cachekey based on request parameters
   *
   * @param array  $params
   * @param string $ret
   **/
  private function make_cache_key($params, &$ret) {
    foreach ($params as $key => $value) {
      if (is_array($value)) {
        // recursive
        $ret .= $key;
        $this->make_cache_key($value, $ret);
      }
      else {
        $ret .= $value;
      }
    }
  }

  /**
   * TingClientRequestCacheInterface::cacheEnable; Check if cache is enabled
   *
   * @return mixed value of variable (drupal_get)
   **/
  public function cacheEnable($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_enable);
  }

  /**
   * Set timeout of cache
   *
   * @return mixed value of variable (variable_get)
   **/
  public function cacheTimeout($value = NULL) {
    $class_name = get_class($this);
    return variable_get($class_name . TingClientRequest::cache_lifetime, '1');
  }

  /**
   * Implements TingClientRequestCacheInterface::cacheBin
   *
   * @return string; name of cachebin
   **/
  public function cacheBin() {
    return 'cache_adhl_webservices';
  }

  /**
   * Implementation of abstract method processResponse
   *
   * @param \stdClass $result
   *
   * @return \stdClass
   */
  public function processResponse(stdClass $result) {
    return $result;
  }
}
