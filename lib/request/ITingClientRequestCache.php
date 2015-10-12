<?php

/**
 * @file  Interface ITingClientRequestCache
 *
 */
interface ITingClientRequestCache {

  public function cacheKey();

  public function cacheEnable($value = NULL);

  public function cacheTimeout($value = NULL);

  public function cacheBin();

  public function checkResponse($response);
}