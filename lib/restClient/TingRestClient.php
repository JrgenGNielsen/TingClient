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
  public $requestBodyString = NULL;

  public function __construct($request) {
    parent::__construct();
    $this->request = $request;
    $headers = $this->get_option(CURLOPT_HTTPHEADER);
    $headers[] = "Accept: application/json";
    $this->set_option(CURLOPT_HTTPHEADER, $headers);
  }

  public function call($action, $params) {
    $outputType = (!empty($params['outputType'])) ? $params['outputType'] : 'json';
    $profile = (!empty($params['profile'])) ? $params['profile'] : NULL;
    $agency = (!empty($params['agency'])) ? $params['agency'] : NULL;
    $curl_options = (!empty($params['curl.options'])) ? $params['curl.options'] : NULL;
    $is_post = (
                 !empty($params['curl.options']) && 
                 !empty($params['curl.options'][CURLOPT_POST]) && 
                 $params['curl.options'][CURLOPT_POST]
               ) ? TRUE : FALSE;
    unset($params['curl.options']);
    if ($curl_options) {
      try {
        $this->set_multiple_options($curl_options);
      }
      catch(TingClientSoapException $e){
        $this->logger->log('request_error', array('Exception : ' => $e->getMessage() ));
      }
    }
    if ($is_post) {
      $url = $this->request->getWsdlUrl();
    }
    else {
      $url = $this->request->getWsdlUrl() . '?' . http_build_query($params);
    }
    $this->set_url($url);
    $response = $this->get();
    $status = $this->get_status();
    if ($status['errno'] != '0') {
      switch ($outputType) {
        case 'xml':
          // TO DO: XML error document.
        default:
          $errorObject = new stdClass();
          $errorObject->error = $status['errno'];
          $errorObject->errorMessage = $status['error'];
          return json_encode($errorObject);
      }
    }
    else if ($status['http_code'] != '200') {
      switch ($outputType) {
        case 'xml':
          // TO DO: XML error document.
        default:
          $errorObject = new stdClass();
          $errorObject->error = $status['http_code'];
          $errorObject->errorMessage = $response;
          return json_encode($errorObject);
      }
      
    }
    return $response;
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
    $post = $this->get_option(CURLOPT_POSTFIELDS);
    if(!empty($post)){
      return $post;
    }
    return 'EMPTY';
  }

}