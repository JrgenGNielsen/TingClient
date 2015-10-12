<?php

/*
 * @file
 * Interface and default implementation of methods extending functionality
 * of ting_client_ckass
 *
 */
interface ItingClientInterFace{
  /**
   * Plugin functionality if a webservice is added outside of the ting-client.
   * NOTICE class is NOT loaded automagically - it must be loaded elsewhere.
   *
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
   *  class and url are mandatory.
   *  xsdNamespace and custom_parse are optional.
   *
   * */
  public function add_to_request_factory($webservice_settings);

  /**
   * Do a named request.
   * @param string $requestName
   *  Name of the request as set in requestfactory
   * @param array $params
   *  Parameters for the request
   * @param bool|TRUE $cache_me
   *  Override other cache settings if needed
   * @return mixed
   *  response from webservice
   */
  public function do_request($requestName, $params, $cache_me=TRUE);

  /**
   * Replace url placeholders with valid urls.
   *
   * @param array $url_values of the type
   *  [placeholder => realurl]  eg.:
   *  array('ting_search_url' => 'http://opensearch.addi.dk/4.0.1/')
   */
  public function sanitize_webservices($url_variables);

  /**
   * support for ting_client_class
   * check if webservice URL, or XSD,  has changed, and update XSD if yes.
   * */
  //function ting_client_webservice_check($check_data);

  /**
   * Get method for caching
   *
   * @param $cache_key
   *  key for cache entry
   * @param $storage;
   *  where to look for the cache
   *
   * */
  //function ting_client_cache_get($cache_key, $storage = NULL);

  /**
   * Set method for caching
   *
   * @param $cache_key
   *  Key for the cache entry
   * @param $data
   *  Data to cache
   * $@param $storage
   *  Locaction to store in
   * @param $expire
   *  Time to cache
   * */
  //function ting_client_cache_set($cache_key, $data, $storage = NULL, $expire = NULL);

  /**
   * Set a message for the client and/or logging
   *
   * @param $message
   *  The message to display
   * @param $type
   *  Type of message eg. Error, status, warning
   * @param $repeat
   *  Whether to repeat the message or not
   * @param $watchdog
   *  Additional parameters if needed
   * */
  //function ting_client_set_message($message = '', $type = 'status', $repeat = NULL, $watchdog = array());

  /**
   * Indicate if cache is enabled
   * @return boolean
   * */
  //function ting_client_enable_cache();


  /**
   * Enable/Disable logging
   * @return Boolean
   * */
  //function ting_client_enable_logging();


  /**
   * Start/Stop a timer
   * @param $action
   *  The action to perform (start/stop)
   * @param $name
   *  Name of the timer to handle
   *
   * */
  //function ting_client_timer($action, $name);


}