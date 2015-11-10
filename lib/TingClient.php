<?php
// include autoload function
require_once __DIR__ . '/ting_client_autoload.php';

/**
 * @file Class TingClient
 * Ting client defines an interface to usage of the TingClient library
 */
class TingClient implements TingClientInterFace {
  /**
   * @var TingClientLogger
   */
  protected $logger;

  /*
   * $var TingClientCacherinterface
   */
  protected $cacher;

  /**
   * Get Request factory.
   *
   * @return TingClientRequestFactory
   *  instance of private memeber request_factory
   */
  private static $requestFactory;
  public function getRequestFactory() {
    return self::$requestFactory;
  }

  /**
   * Constructor
   *
   * @param \TingClientRequestCacheInterface|NULL $cacher
   * @param \TingClientLogger|NULL                $logger
   */
  public function __construct(TingClientCacherInterface $cacher = NULL, TingClientLogger $logger = NULL) {
    $this->logger = (isset($logger)) ? $logger : new TingClientVoidLogger();
    $this->cacher = (isset($cacher)) ? $cacher : new TingClientCacher();
    self::$requestFactory = new TingClientRequestFactory();
  }

  /**
   * Execute a request.
   *
   * @param \TingClientRequest $request
   *
   * @return string
   *  Response of request
   * @throws \TingClientSoapException
   */
  public function execute(TingClientRequest $request) {
    // check cache
    $cache_key = $request->cacheKey();
    if ($this->cacher->get($cache_key)) {
      return $this->cacher->get($cache_key);
    }
    // not found in cache - get the client to do the real call
    try {
      $soapCLient = $this->getSoapClient($request);
    }
    catch(TingClientSoapException $e){
      $this->logger->log('soap_request_error',array('Exception : ' => $e->getMessage() ));
    }
    $action = $request->getParameter('action');
    $request->unsetParameter('action');
    $params = $request->getParameters();
    $this->logger->startTime();
    $response = $soapCLient->call($action, $params);
    $this->logger->stopTime();
    if ($response !== FALSE) {
      $log = array(
        'action' => $action,
        'requestBody' => $soapCLient->requestBodyString,
        'wsdlUrl' => $request->getWsdlUrl(),
      );
      $this->logger->log('soap_request_complete', $log);
      $this->cacher->set($cache_key, $response);
    }
    else{
      $this->logger->log('soap_request_error',array('Message : ' => 'SOMETHING WENT WRONG' ));
    }
    return $response;
  }


  /**
   * Do a named request.
   *
   * @param string    $requestName
   *  Name of the request as set in requestfactory
   * @param array     $params
   *  Parameters for the request
   * @param bool|TRUE $cache_me
   *  Override other cache settings if needed
   *
   * @return mixed
   *  response from webservice
   */
  public function doRequest($requestName, $params, $cache_me = TRUE) {
    $request = $this->getRequestFactory()
      ->getNamedRequest($requestName, $params);
    $result = $this->execute($request);

    return $result;
  }

  /**
   * Add webservices to requestfactory. @see lib/request/TingClientRequestFactory
   *
   * @param $webservice_settings
   *  Array describing the webservice eg.
   *
   *  <name> => array(<url>,<class><xsdNamespace><custom_parse>)
   *
   *  Example:
   *
   *  $ret['forsrights']['class'] = 'bibdk_forsrights';
   *  $ret['forsrights']['url'] = 'bibdk_forsrights_url';
   *  $ret['forsrights']['xsdNamespace'] = array(0=>'http://oss.dbc.dk/ns/forsrights');
   *  $ret['forsrights']['custom_parse'] = bibdk_forsrights_parse_response
   *
   *  class and url are required.
   *  xsdNamespace and custom_parse are optional.
   *
   * */
  public function addToRequestFactory($webservice_settings = array()) {
    $this->getRequestFactory()->addToUrls($webservice_settings);
  }

  /**
   * Set real urls in request factory.
   *
   * @param array $real_urls
   *  Array with the real urls: [name => [placeholder => real_url]]
   *  e.g
   *  array('forsrights' => array(
   *    'bibdk_forsrights_url' => 'http://forsrights.addi.dk/1.2/',
   *    'bibdk_forsrights_xsd' => 'http://forsrights.addi.dk/1.2/forsrights.xsd',
   *    ),
   *  );
   */
  public function setRealUrls ($real_urls = array()){
    $this->getRequestFactory()->setRealUrls($real_urls);

  }

  /**
   * Set private member cacher
   *
   * @param \TingClientCacherInterface $cacher
   */
  public function setCacher(TingClientCacherInterface $cacher) {
    $this->cacher = $cacher;
  }

  /**
   * Set private member logger
   *
   * @param \TingClientLogger $logger
   */
  public function setLogger(TingClientLogger $logger) {
    $this->logger = $logger;
  }

  /**
   * Get the client appropiate for handling given request.
   *
   * @param \TingClientRequest $request
   *
   * @return \TingNanoClient|\TingSoapClient
   * @throws \TingClientSoapException
   */
  private function getSoapClient(TingClientRequest $request) {
    switch ($request->getClientType()) {
      case 'NANO':
        $options = array('namespaces' => $request->getXsdNameSpace());
        return new TingNanoClient($request->getWsdlUrl(), $options);
      case 'SOAPCLIENT';
        return new TingSoapClient($request);
      default:
        $class_name = get_class($request);
        throw new TingClientSoapException($class_name . ' Request does not define a valid client type');
    }
  }
}