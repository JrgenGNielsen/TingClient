<?php

/**
 * @file Class TingClient
 */

class TingClient {
  /**
   * @var TingClientLogger
   */
  private $logger;
  /*
   * $var $cacher
   */
  private $cacher;

  /**
   * Get Request factory. Request factory is handled as singleton
   *
   * @return TingClientRequestFactory
   *  instance of private memeber request_factory
   */
  private static $request_factory;
  public function request_factory() {
    if (!isset(self::$request_factory)) {
      self::$request_factory = new TingClientRequestFactory();
    }
    return self::$request_factory;
  }

  public function __construct(ITingClientRequestCache $cacher = NULL,  TingClientLogger $logger = NULL) {
    $this->logger = (isset($logger)) ? $logger : new TingClientVoidLogger();
    $this->cacher = (isset($cacher)) ? $cacher : new TingClientCacher();
  }

  public function execute(TingClientRequest $request) {
    // check cache
    $cache_key = $request->cacheKey();
    if($this->cacher->get($cache_key)){
      return $this->cacher->get($cache_key);
    }

    // not found in cache - get the client to do the real call
    $soapCLient = $this->getSoapClient($request);
    $requestAdapter = new TingClientRequestAdapter($soapCLient);
    $response = $requestAdapter->execute($request);

    $this->cacher->set($cache_key, $response);
    return $response;
  }

  public function setCacher(ITingClientCacherInterface $cacher){
    $this->cacher = $cacher;
  }

  public function setLogger(TingClientLogger $logger){
    $this->logger = $logger;
  }

  /**
   * Get the client appropiate for handling given request.
   *
   * @param \TingClientRequest $request
   * @return \TingNanoClient|\TingSoapClient
   * @throws \TingClientSoapException
   */
  private function getSoapClient(TingClientRequest $request){
    switch($request->getClientType()){
      case 'NANO':
        $options = array('namespaces' => $request->getXsdNameSpace());
        return new TingNanoClient($request->getWsdlUrl(), $options);
      case 'SOAPCLIENT' ;
        return new TingSoapClient($request);
      default:
        $class_name = get_class($request);
        throw new TingClientSoapException($class_name . ' Request does not define a valid client type');
    }
  }
}