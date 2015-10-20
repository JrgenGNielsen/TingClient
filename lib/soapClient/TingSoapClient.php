<?php

/**
 * Class TingSoapClient.
 *
 * Wraps php soapclient in a class.
 */
class TingSoapClient implements TingSoapClientInterface {
  /**
   * @var \SoapClient
   */
  private $soapClient;
  /**
   * @var string
   */
  public $requestBodyString;
  // this is for integration with ting-client
  // @see tingClientRequestAdapter, @see contrib/nanosoap.inc
  /**
   * @var array
   */
  private $curl_info;
  // for test purpose
  /**
   * @var string
   */
  public static $user_agent;

  /**
   * Constructor. Initialize soapclient.
   *
   * @param TingCLientRequest     $request
   * @param string $location
   *
   * @throws \SoapFault
   */
  public function __construct($request, $location = NULL) {
    // get uri of wsdl
    $wsdl = $request->getWsdlUrl();
    // soapClient is set with trace and exception options to enable proper exceptionhandling and logging
    $options = array(
      'trace' => 1,
      'exceptions' => 1,
      'soap_version' => SOAP_1_1,
      'cache_wsdl' => WSDL_CACHE_NONE,
    );

    if (isset(self::$user_agent)) {
      $options += array('user_agent' => self::$user_agent);
    }

    if (!empty($location)) {
      $options += array('location' => $location);
    }
    // xdebug causes a fatal error before soapclient handles error in constructor
    // disable it. it shouldn't be in production anyways
    if (function_exists('xdebug_disable')) {
      xdebug_disable();
    }
    // constructor causes an php i/o warning on failure. suppress it (@)
    $this->soapClient = @new SoapClient($wsdl, $options);

    if (!is_object($this->soapClient) || is_soap_fault($this->soapClient)) {
      throw new SoapFault('500', 'SoapClientFault:Failed to construct. WSDL location is:' . $wsdl);
    }
  }

  /**
   * Wrapper for execution of scoapclient.
   * this is for integration with ting-client
   *
   * @param string $action ; the method to execute
   * @param mixed  $params ; paramters for method
   *
   * @return mixed bool | stdClass
   */
  public function call($action, $params) {
    try {
      $data = $this->soapClient->$action($params);
    } catch (Exception $e) {
      // set status code to 400 (bad request)
      $this->setCurlInfo('400');
      return FALSE;
    }

    // all went well
    $this->requestBodyString = $this->soapClient->__getLastRequest();
    $this->setCurlInfo();

    return $data;
  }

  /**
   * Return status for request
   * this is for integration with ting-client
   *
   * @see tingClientRequestAdapter, @see contrib/nanosoap.inc
   *
   * @return array
   *  private member curl_info
   */
  public function getCurlInfo() {
    return $this->curl_info;
  }

  /**
   * Set private member curl_info with given errorcode. If errorcode is not set
   *  it is assumed that the request has completed, and the curl_info is set
   *  from response headers.
   *
   * for integration with ting-client
   *
   * @param int|null $errorcode;
   */
  private function setCurlInfo($errorcode = NULL) {
    if (!empty($errorcode)) {
      $this->curl_info = array('http_code' => $errorcode);
      return;
    }
    $responseHeaders = $this->soapClient->__getLastResponseHeaders();
    $this->curl_info = $this->parseResponseHeader($responseHeaders);
    return;
  }

  /**
   * Check errorcode in responseheader.
   *
   * @param string $headerstring. Responsehader from soapclient
   *
   * @return array
   */
  private function parseResponseHeader($headerstring) {
    if (strpos($headerstring, 'HTTP/1.1 200 OK') !== FALSE) {
      return array('http_code' => '200');
    }
    // status code MUST be 200
    return array('http_code' => '500');
  }
}
