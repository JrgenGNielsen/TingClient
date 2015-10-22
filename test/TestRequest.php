<?php
class TestRequest extends TingClientRequest {
  public $clientType;

  public function __construct($wsdlUrl, $clientype = 'NANO') {
    $this->clientType = $clientype;
    parent::__construct($wsdlUrl);
  }

// overwrite parent method
  public function getClientType() {
    return $this->clientType;
  }

  public function cacheEnable($value = NULL) {
    return TRUE;
  }

  public function cacheTimeout($value = NULL) {
    return $_SERVER['REQUEST_TIME'] + 1;
  }

  public function processResponse(stdClass $response) {
    return $this->parseResponse($response);
  }
}