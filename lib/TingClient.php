<?php

require_once 'ting_client_autoload.php';

/**
 * @file Class TingClient
 * Ting client defines an interface to usage of the TingClient library
 */
class TingClient implements TingClientInterFace {
  /**
   * @var TingClientLogger
   */
  private $logger;

  /*
   * $var TingClientCacherinterface
   */
  private $cacher;

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
  public function __construct(TingClientRequestCacheInterface $cacher = NULL, TingClientLogger $logger = NULL) {
    $this->logger = (isset($logger)) ? $logger : new TingClientVoidLogger();
    $this->cacher = (isset($cacher)) ? $cacher : new TingClientCacher();
    self::$requestFactory = new TingClientRequestFactory();

    print __FILE__;
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

    // @TODO try catch
    $soapCLient = $this->getSoapClient($request);
    $action = $request->getParameter('action');
    $request->unsetParameter('action');
    $params = $request->getParameters();
    $response = $soapCLient->call($action, $params);

    $this->cacher->set($cache_key, $response);
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
    $this->sanitizeWebservices();
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
    $this->getRequestFactory()->add_to_urls($webservice_settings);
  }

  /**
   * Replace url placeholders with valid urls.
   *
   * @param array $url_values of the type
   *  [placeholder => realurl]  eg.:
   *  array('search' => array('ting_search_url' => 'http://opensearch.addi.dk/4.0.1/')),
   *
   */
  public function sanitizeWebservices($real_urls = array()) {
    $url_variables = $real_urls;
    // merge in default urls
    $url_variables += TingClientWebserviceSettings::getDefaultUrls();
    foreach ($url_variables as $name => $url) {
      if (!$url) {
        throw new Exception('ting-client: Webservice URL is not defined for ' . $name);
      }
      $this->getRequestFactory()->set_real_urls($name, $url);
    }
  }


  /**
   * Set private member cacher
   *
   * @param \ITingClientCacherInterface $cacher
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
   * @param \TingClientRequestInterface $request
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