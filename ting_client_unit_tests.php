<?php
require_once 'TingClientBaseClass.php';

class TestCustomCacher implements ITingClientCacherInterface {
  private static $cache = array();

  function set($key, $value, $storage = NULL, $expire = NULL) {
    self::$cache[$key] = $value;
  }

  function get($key) {
    return isset(self::$cache[$key]) ? self::$cache[$key] : FALSE;
  }

  function clear() {
    self::$cache = array();
  }
}

class TestCustomLogger extends TingClientLogger {
  protected function doLog($message, $variables, $severity) {
    print 'LOGGING';
  }
}

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

class TestTingClientClass extends PHPUnit_Framework_TestCase {
  /**
   * Test that all objects has been loaded with ting_client_class
   */
  public function test_objects() {
    $ting_class = new TingClientBaseClass();
    $this->assertTrue(is_object($ting_class), 'ting client class initialized');

    $client = $ting_class->tingClient();
    $this->assertTrue($client instanceof TingClient, 'ting client initialized');

    $url = 'hest';
    $soapclient = new TingNanoClient($url);
    $this->assertTrue(is_object($soapclient), 'nano client initialized');

    // test chat custom cachder can be set
    $ting_class->setCacher(new TestCustomCacher());
    $client = $ting_class->tingClient();
    $obj = new ReflectionObject($client);
    $cacher = $obj->getProperty('cacher');
    $cacher->setAccessible(TRUE);
    $this->assertTrue($cacher->getValue($client) instanceof TestCustomCacher, 'custom cache set');

    // test getSoapCLient method
    $method = $obj->getMethod('getSoapClient');
    $method->setAccessible(TRUE);

    $url = 'http://forsrights.addi.dk/1.2/forsrights.wsdl';
    $request = new TestRequest($url, 'NANO');

    $soap = $method->invokeArgs($client, array($request));
    $this->assertTrue($soap instanceof TingNanoClient);

    $request = new TestRequest($url, 'SOAPCLIENT');
    $soap = $method->invokeArgs($client, array($request));
    $this->assertTrue($soap instanceof TingSoapClient);

    $request = new TestRequest($url, 'HEST');
    try {
      $soap = $method->invokeArgs($client, array($request));
    } catch (Exception $e) {
      $this->assertTrue($e instanceof TingClientSoapException);
    }

    // test that custom logger can be set
    $ting_class->setLogger(new TestCustomLogger());
    $client = $ting_class->tingClient();
    $obj = new ReflectionObject($client);
    $logger = $obj->getProperty('logger');
    $logger->setAccessible(TRUE);
    $this->assertTrue($logger->getValue($client) instanceof TestCustomLogger, 'custom logger set');
  }

  public function test_request() {
    // test request
    $url = 'http://forsrights.addi.dk/1.2/';
    $request = new TestRequest($url);
    $this->assertTrue($request instanceof TingClientRequest);


    $params = array(
      'action' => 'HEST',
      'fisk' => 'guppy',
      'fugle' => array(
        'stær',
        'spurv',
      )
    );
    $request->setParameters($params);

    // assert that parameters has been set
    $this->assertEquals($request->getParameters(), $params, 'parameters set and get success');

    // test cachekey function
    $key = md5('actionHESTfiskguppyfugle0stær1spurv');
    $this->assertTrue($request->cacheKey() == $key, 'cache key set as expected');

    // assert that trackingId does NOT alter cachekey
    $request->setParameter('trackingId', '123456');
    $this->assertTrue($request->cacheKey() == $key, 'trackingId skipped in cachekey');

    // test correct response
    $response = file_get_contents('test_mockups/forsrights_response.string');
    //$request->parseResponse($response);
    // $this->assertTrue($response == $parsed_response);

    // test bad json - throws a TingClientException
    $response = file_get_contents('test_mockups/bad_json.string');
    try {
      $request->parseResponse($response);
    } catch (Exception $e) {
      ;
      $this->assertTrue($e instanceof TingClientException);
    }

    // test for faultstring
    $response = file_get_contents('test_mockups/forsrights_fault.string');
    try {
      $request->parseResponse($response);
    } catch (Exception $e) {
      $this->assertTrue($e instanceof TingClientException);
    }
  }

  public function test_functions() {
    // test that webservices can be added and configured
    $ting_class = new TingClientBaseClass();
    $addi_urls = array(
      'forsrights' => array(
        'class' => 'bibdk_forsrights',
        'url' => 'bibdk_forsrights_url',
        'xsdNamespace' => array(0 => 'http://oss.dbc.dk/ns/forsrights'),
        'xsd_url' => 'bibdk_forsrights_xsd',
      ),
    );

    // assert that real urls can be set
    $ting_class->add_to_request_factory($addi_urls);
    $url = 'http://forsrights.addi.dk/1.2/';
    $xsd_url = 'http://forsrights.addi.dk/1.2/forsrights.xsd';
    $real_urls = array(
      'forsrights' => array(
        'bibdk_forsrights_url' => $url,
        'bibdk_forsrights_xsd' => $xsd_url,
      )
    );
    $ting_class->sanitize_webservices($real_urls);

    $this->assertTrue($ting_class->tingClient()
        ->request_factory()->urls['forsrights']['url'] == $url, 'url was sanitized');
    $this->assertTrue($ting_class->tingClient()
        ->request_factory()->urls['forsrights']['xsd_url'] == $xsd_url, 'xsd url was sanitized');

    // assert that parameters are ordered by given xsd
    $params = array(
      'action' => 'forsRightsRequest',
      'userIdAut' => 'netpunkt',
      'groupIdAut' => '010100',
      'passwordAut' => '20Koster',
      'outputType' => 'json',
    );
    $request1 = $ting_class->tingClient()
      ->request_factory()
      ->getNamedRequest('forsrights', $params);

    $params = array(
      'passwordAut' => '20Koster',
      'userIdAut' => 'netpunkt',
      'groupIdAut' => '010100',
      'action' => 'forsRightsRequest',
      'outputType' => 'json',
    );
    $request2 = $ting_class->tingClient()
      ->request_factory()
      ->getNamedRequest('forsrights', $params);

    $this->assertEquals($request1->getParameters(), $request2->getParameters(), 'parameters was reorganized according to xsd');

    //assert that xsd was stored in temporary folder
    $file_name = TingClientCommon::url_to_filename($xsd_url);
    $dir = sys_get_temp_dir();
    $this->assertTrue(file_exists($dir . '/' . $file_name), 'xsd file stored in temp folder');
  }
}
