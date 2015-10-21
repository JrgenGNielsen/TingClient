<?php

/**
 * @file
 * Class TingClientRequestFactory
 *
 * Handle retrieval of requests.
 */
class TingClientRequestFactory {

  /**
   * @var array urls.
   *  Settings for the requests
   */
  public $urls;

  private $realUrls = array();

  public function __construct() {
    $urls = TingClientWebserviceSettings::getInlineServices();
    $this->urls = $urls;
  }

  public function setRealUrls ($real_urls) {
    $this->realUrls = $real_urls;
  }

  /**
   * Add given urls (webservice definitions).
   *
   * @param array $urls
   *  array of webservice settings of the form:
   *
   *  <name> => array(<url>,<class><xsdNamespace><custom_parse>)
   *
   *  Example:
   *
   *  $ret['forsrights']['class'] = 'bibdk_forsrights';
   *  $ret['forsrights']['url'] = 'bibdk_forsrights_url';
   *  $ret['forsrights']['xsdNamespace'] = array(0=>'http://oss.dbc.dk/ns/forsrights');
   *  $ret['forsrights']['custom_parse'] = bibdk_forsrights_parse_response
   */
  public function addToUrls(array $urls) {
    //overwrite inline urls - they might be outdated
    $this->urls = $urls;
    // merge in inline urls
    $this->urls += TingClientWebserviceSettings::getInlineServices();
  }

  /**
   * Replace url placeholders with valid urls.
   *
   * @param array $url_values of the type
   *  [placeholder => realurl]  eg.:
   *  array('search' => array('ting_search_url' => 'http://opensearch.addi.dk/4.0.1/')),
   *
   */
  public function sanitizeWebservices() {
    $url_variables = $this->realUrls;
    // merge in default urls
    $url_variables += TingClientWebserviceSettings::getDefaultUrls();
    foreach ($url_variables as $name => $url) {
      if (!$url) {
        throw new Exception('ting-client: Webservice URL is not defined for ' . $name);
      }
      $this->sanitizeUrls($name, $url);
    }
  }


  /**
   * Return object($className) if it exists and url is set, else throw TingClientException
   *
   * @param array $params
   *  parameters for the request
   * @param string $name ,
   *  the name of the request (for mapping in $urls variable)
   *
   **/
  public function getNamedRequest($name, $params) {
    if (empty($this->urls[$name]) || empty($this->urls[$name]['class'])) {
      throw new TingClientException('No webservice defined for ' . $name);
    }
    $this->sanitizeWebservices();
    $class = $this->urls[$name]['class'];
    if (class_exists($class) && !empty($this->urls[$name]['url'])) {
      $request = new $class($this->urls[$name]['url']);
      // check xsd file
      if (isset($this->urls[$name]['xsd_url'])) {
        $params = TingClientCommon::checkXsd($this->urls[$name]['xsd_url'], $params);
      }
      if (isset($this->urls[$name]['xsdNamespace'])) {
        $request->setXsdNameSpace($this->urls[$name]['xsdNamespace']);
      }
      $request->setParameters($params);
      return $request;
    }
    throw new TingClientException('No webservice url defined for ' . $name);
  }


  /**
   * Get a webservice definition
   *
   * @param string $name
   *
   * @return array
   *  Webservice setttings for given name
   */
  public function getSettings($name) {
    return $this->urls[$name];
  }

  /**
   * Get urls to xml schema definitions for webservices defined in factory
   *
   * @return array xsdUrls
   *  All xsdurls is factory
   */
  public function getXSDurls() {
    $xds_urls = array();
    foreach ($this->urls as $key => $value) {
      if (!empty($value['xsd_url'])) {
        $xds_urls[$key] = $value['xsd_url'];
      }
    }
    return $xds_urls;
  }

  /**
   * Get all webservice definitions
   *
   * @return array
   *  Settings for webservices
   *
   */
  public function getUrls() {
    return $this->urls;
  }

  /**
   * Replace placeholders in webservice definitions with the real url's
   *
   * @param string $name .
   *  The name of the webservice.
   * @param array  $url_variables .
   *  the real urls to replace with.
   */
  public function sanitizeUrls($name, array $url_variables) {
    $settings = $this->urls[$name];
    foreach ($settings as $key => $placeholder) {
      if (is_array($placeholder)) {
        continue;
      }
      if (isset($url_variables[$placeholder])) {
        $this->urls[$name][$key] = $url_variables[$placeholder];
      }
    }
  }
}
