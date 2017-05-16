<?php

class bibdk_forsrights extends TingClientRequest {

  public function cacheEnable($value = NULL) {
    return TRUE;
  }

  public function cacheTimeout($value = NULL) {
    // TODO: Implement cacheTimeout() method.
    return $_SERVER['REQUEST_TIME'] + 1;

  }

  /**
   * Get location of cache.
   * @return string
   *  Name of location
   */
  public function cacheBin() {
    return 'cache_forsrights_webservice';
  }

  /**
   *
   * @param \stdClass $result
   * @return mixed
   * @throws \TingClientException
   */
  public function processResponse(stdClass $result) {
    return $this->parseResponse($result);
  }
}