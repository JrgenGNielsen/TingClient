<?php

/**
 * @file  Interface TingClientRequestCacheInterface
 *
 */
interface TingClientRequestCacheInterface {

  /**
   * @return string
   */
  public function cacheKey();

  /**
   * @param null $value
   *
   * @return bool
   */
  public function cacheEnable($value = NULL);

  /**
   * @param null $value
   *
   * @return string
   */
  public function cacheTimeout($value = NULL);

  /**
   * @return string
   */
  public function cacheBin();

  /**
   * @param $response
   *
   * @return mixed
   */
  public function checkResponse($response);
}