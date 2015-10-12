<?php

class bibdk_forsrights extends TingClientRequest {

  public function cacheEnable($value = NULL) {
    return TRUE;
  }

  public function cacheTimeout($value = NULL) {
    // TODO: Implement cacheTimeout() method.
    return $_SERVER['REQUEST_TIME'] + 1;

  }

  /* \brief implements ITingClientRequestCache::cacheBin
   * 
   * @return string; name of cachebin
   */

  public function cacheBin() {
    return 'cache_forsrights_webservice';
  }

  // empty;
  public function processResponse(stdClass $result) {
    return $result;
  }

}