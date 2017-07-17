<?php

/*
 * @file
 * Interface and default implementation of methods extending functionality
 * of ting_client_ckass
 *
 */

interface TingClientInterFace {
  /**
   * Plugin functionality if a webservice is added outside of the ting-client.
   * NOTICE class is NOT loaded automagically - it must be loaded elsewhere.
   *
   * Add webservices to request factory. @see lib/request/TingClientRequestFactory
   *
   * @param array $webservice_settings
   *  Array describing the webservice eg.
   *
   *  <name> => array(<url>,<class><xsdNamespace><custom_parse>)
   *
   *  Example:
   *
   *  $ret['forsrights']['class'] = 'bibdk_forsrights';
   *  $ret['forsrights']['url'] = 'bibdk_forsrights_url';
   *  $ret['forsrights']['xsdNamespace'] = array(0 => 'http://oss.dbc.dk/ns/forsrights');
   *  $ret['forsrights']['custom_parse'] = bibdk_forsrights_parse_response
   *
   *  class and url are mandatory.
   *  xsdNamespace and custom_parse are optional.
   *
   * */
  public function addToRequestFactory($webservice_settings);

  /**
   * Do a named request.
   *
   * @param string    $requestName
   *  Name of the request as set in requestfactory
   * @param array     $params
   *  Parameters for the request
   *
   * @return mixed
   *  response from webservice
   */
  public function doRequest($requestName, $params);

  /**
   * Replace url placeholders with valid urls.
   *
   * @param array $url_values of the type
   *  [placeholder => realurl]  eg.:
   *  array('ting_search_url' => 'http://opensearch.addi.dk/4.0.1/')
   */
  //public function sanitizeWebservices($url_variables);

}