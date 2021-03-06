<?php

/**
 * @file
 * Class TingNanoClient
 *
 * Default implementation
 */
class TingNanoClient extends NanoSOAPClient implements TingClientAgentInterface {

  public function __construct($endpoint, $options = array()) {
    parent::__construct($endpoint, $options);
  }

  /**
   * Return requestBodyString.
   *
   * @return string
   */
  public function getRequestBodyString() {
    return $this->requestBodyString;
  }

}