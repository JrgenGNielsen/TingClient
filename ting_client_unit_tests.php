<?php
require_once 'ting-client/ting_client_class.php';

class TestCustomCacher implements ITingClientCacherInterface{
  private static $cache = array();
  function set($key, $value, $storage = NULL, $expire = NULL) {
    self::$cache[$key] = $value;
  }

  function get($key) {
    return isset(self::$cache[$key]) ? self::$cache[$key] : FALSE;
  }

  function clear(){
    self::$cache = array();
  }
}

class TestCustomLogger extends TingClientLogger{
  protected function doLog($message, $variables, $severity) {
    print 'LOGGING';
  }
}

class TestTingClientClass extends PHPUnit_Framework_TestCase {
  /**
   * Test that all objects has been loaded with ting_client_class
   */
  public function test_objects() {
    $ting_class = new ting_client_class();
    $this->assertTrue(is_object($ting_class), 'ting client class initialized');



    $client = $ting_class->tingClient();
    $this->assertTrue($client instanceof TingClient, 'ting client initialized');

    $url = 'hest';
    $soapclient = new TingNanoClient($url);
    $this->assertTrue(is_object($soapclient), 'nano client initialized');

    $adapter = new TingClientRequestAdapter($soapclient);
    $this->assertTrue(is_object($adapter), 'requestadapter initialized');

    // test chat custom cachder can be set
    $ting_class->setCacher(new TestCustomCacher());
    $client = $ting_class->tingClient();
    $obj = new ReflectionObject($client);
    $cacher = $obj->getProperty('cacher');
    $cacher->setAccessible(true);
    $this->assertTrue($cacher->getValue($client) instanceof TestCustomCacher, 'custom cache set');

    // test that custom logger can be set
    $ting_class->setLogger(new TestCustomLogger());
    $client = $ting_class->tingClient();
    $obj = new ReflectionObject($client);
    $logger = $obj->getProperty('logger');
    $logger->setAccessible(true);
    $this->assertTrue($logger->getValue($client) instanceof TestCustomLogger, 'custom logger set');
  }

  public function test_functions() {
    // test that webservices can be added and configured
    $ting_class = new ting_client_class();
    $addi_urls = array(
      'forsrights' => array(
        'class' => 'bibdk_forsrights',
        'url' => 'bibdk_forsrights_url',
        'xsdNamespace' => array(0 => 'http://oss.dbc.dk/ns/forsrights'),
      ),
    );

    $ting_class->add_to_request_factory($addi_urls);
    $url = 'http://forsrights.addi.dk/1.2/';
    $real_urls = array('forsrights' => array('bibdk_forsrights_url' => $url));
    $ting_class->sanitize_webservices($real_urls);

    $this->assertTrue($ting_class->tingClient()->request_factory()->urls['forsrights']['url'] == $url, 'url was sanitized');
  }
}
