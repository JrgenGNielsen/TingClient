<?php

/**
 * @file
 * Class TingRestClient
 *
 */

 /**
 * Constructor. Initialize TingRestClient.
 *
 * @param TingCLientRequest     $request
 * @param string $location
 *
 * @throws \SoapFault
 */
class TingRestClient extends MicroCURL implements TingClientAgentInterface {

  /**
   * @var TingClientRequest
   */
  protected $request;

  /**
   * The XML string sent as part of a request. NULL in a REST call.
   *
   * @var string
   */
  public $requestBodyString;

  public function __construct($request) {
    parent::__construct();
    $this->request = $request;
    $headers = $this->get_option(CURLOPT_HTTPHEADER);
    $headers[] = "Accept: application/json";
    $this->set_option(CURLOPT_HTTPHEADER, $headers);
  }

  public function call($action, $params) {
    $url = $this->request->getWsdlUrl() . '?' . http_build_query($params);
    $this->set_url($url);
    return $this->get();
  }

  public function getCurlInfo() {
    return $this->get_status();
  }

  /**
   * Return requestBodyString (for consistency with TingNanoClient).
   *
   * @return string
   */
  public function getRequestBodyString() {
    return NULL;
  }

}