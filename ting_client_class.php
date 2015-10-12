<?php

require_once 'ting_client_autoload.php';

/**
 * Class ting_client_class
 * This is a framework (drupal) specific implementation of ting_client_class-
 * so framework methods are okay here.
 *
 */

class ting_client_class implements ItingClientInterface {

  /**
   * Get the Client. TingClient is handled as singleton.
   *
   * @return TingClient
   *  instance of private member tingClient
   */
  private static $tingClient;
  public function tingClient(){
    if(!isset(self::$tingClient)){
      self::$tingClient = new TingClient();
    }
    return self::$tingClient;
  }

  public function setCacher(ITingClientCacherInterface $cacher){
    $this->tingClient()->setCacher($cacher);
  }

  public function setLogger(TingClientLogger $logger){
    $this->tingClient()->setLogger($logger);
  }


  /**
   * Add weservices to requestfactory. @see lib/request/TingClientRequestFactory
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
  public function add_to_request_factory($webservice_settings = array()) {
    $this->tingClient()->request_factory()->add_to_urls($webservice_settings);
  }

  /**
   * Replace url placeholders with valid urls.
   *
   * @param array $url_values of the type
   *  [placeholder => realurl]  eg.:
   *  array('search' => array('ting_search_url' => 'http://opensearch.addi.dk/4.0.1)/',
   *        ('   )
   */
  public function sanitize_webservices($real_urls = array()) {
    $url_variables = $real_urls;
    // merge in default urls
    $url_variables += TingClientWebserviceSettings::getDefaultUrls();
    foreach ( $url_variables as $name=>$url ) {
      if ( !$url ) {
        throw new Exception( 'ting-client: Webservice URL is not defined for ' . $name);
      }
      $this->tingClient()->request_factory()->set_real_urls($name, $url);
    }
  }

  /**
   * Do a named request.
   *
   * @param string $requestName
   *  Name of the request as set in requestfactory
   * @param array $params
   *  Parameters for the request
   * @param bool|TRUE $cache_me
   *  Override other cache settings if needed
   * @return mixed
   *  response from webservice
   */
  public function do_request($requestName, $params, $cache_me = TRUE) {

    // @TODO move this to framework specific extending class
    $this->sanitize_webservices();

    $request = $this->tingClient()->request_factory()->getNamedRequest($requestName, $params);
    //$request->setParameters($params);

    $client = $this->tingClient();
    $result = $client->execute($request);

    return $result;
  }
}