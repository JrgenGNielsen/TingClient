<?php

class TingClientRequestFactory {

  public $urls;

  public function __construct() {
    $urls = TingClientWebserviceSettings::getInlineServices();
    $this->urls = $urls;
  }

  /**
   * Add given urls (webservice definitions).
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
  public function add_to_urls($urls) {
    $this->urls += $urls;
  }

  /**
   * Return object($className) if it exists and url is set, else throw TingClientException
   *
   * @className,
   *  the class implementing the request
   * @name,
   *  the name of the request (for mapping in $urls variable)
   *
   **/
  public function getNamedRequest($name, $params) {
    if (empty($this->urls[$name]) || empty($this->urls[$name]['class'])) {
      throw new TingClientException('No webservice defined for ' . $name);
    }
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
   * @param $name
   * @return mixed
   */
  public function getSettings($name){
    return $this->urls[$name];
  }

  /**
   * Get urls to xml schema definitions for webservices defined in factory
   * @return array
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
   * @return array
   *
   */
  public function getUrls() {
    return $this->urls;
  }

  /**
   * Replace placeholders in webservice definitions with the real url's
   * @param string $name .
   *  The name of the webservice.
   * @param array $url_variables .
   *  the real urls to replace with.
   */
  public function set_real_urls($name, array $url_variables) {
    $settings = $this->urls[$name];
    foreach ($settings as $key => $placeholder) {
      if(is_array($placeholder)){
        continue;
      }
      if(isset($url_variables[$placeholder])){
        $this->urls[$name][$key] = $url_variables[$placeholder];
      }
    }
  }
}
