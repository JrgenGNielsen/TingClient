<?php

/**
 * @file Class TingClientRequest
 * Base class for requests. Extending methods must implement remaainder methods
 * of ITingClientRequestCache and abstract method processResponse
 */
abstract class TingClientRequest implements ITingClientRequestCache{

  /* suffixes to use for cache variables */
  const cache_lifetime = '_cache_lifetime';
  const cache_enable = '_cache_enable';

  // for tracing the request
  private $trackingId;

  /* attributes to be used by extending classes */
  protected $cacheKey;
  private $nameSpace;
  private $xsdNameSpace;
  private $wsdlUrl;
  protected $parameters = array();

  abstract public function processResponse(stdClass $response);

  public function __construct($wsdlUrl, $serviceName = NULL) {
    $this->wsdlUrl = $wsdlUrl;
    //$this->wsdlUrl = $wsdlUrl;
  }

  /** \brief make a cachekey based on request parameters
   *
   * @param array $params
   * @param string $ret
   **/
  private function make_cache_key($params, $ret='') {
    foreach ($params as $key => $value) {
      // skip trackinId
      if ($key === 'trackingId') {
        continue;
      }

      $ret .= $key;
      if (is_array($value)) {
        // recursive
        $ret = $this->make_cache_key($value, $ret);
      }
      else {
        $ret .= $value;
      }
    }
    return $ret;
  }

  public function setXsdNameSpace(array $value){
    $this->xsdNameSpace = $value;
  }
  public function getXsdNameSpace(){
    return !empty($this->xsdNameSpace) ? $this->xsdNameSpace : FALSE;
  }


  public function getClientType(){
    return 'NANO';
  }

  public function checkResponse($response){
    return TRUE;
  }

  // default implementation of ITingClientRequestCache::cacheBin
  // extending request can implement this method if it wishes it's own bin
  public function cacheBin() {
    return 'cache_bibdk_webservices';
  }


  /** default Implementation of ITingClientRequestCache::cacheKey
   *
   * @return string
   **/
  public function cacheKey() {
    $params = $this->getParameters();
    $ret = $this->make_cache_key($params);
    return md5($ret);
  }

  public function getWsdlUrl(){
    return $this->wsdlUrl;
  }

  public function getClassname() {
    return get_class($this);
  }

  public function setParameter($name, $value) {
    $this->parameters[$name] = $value;
  }

  public function unsetParameter($name) {
    if (isset($this->parameters[$name])) {
      unset($this->parameters[$name]);
    }
  }

  public function getParameter($name) {
    return $this->parameters[$name];
  }

  public function setParameters($array) {
    $this->parameters = $array;
  }


  public function getParameters() {
    return $this->parameters;
  }


  public function parseResponse($responseString) {
    $response = json_decode($responseString);

    if (!$response) {
      $faultstring = self::parseForFaultString($responseString);
      if (isset($faultstring)) {
        throw new TingClientException($faultstring);
      }
      else {
        throw new TingClientException('Unable to decode response as JSON: ' . $responseString);
      }
    }

    if (!is_object($response)) {
      throw new TingClientException('Unexpected JSON response: ' . var_export($response, true));
    }
    return $this->processResponse($response);
  }

  /** \brief response from webservice is ALWAYS xml if validation fails
   * elemants <faultCode> and <faultString> will be present in that case
   * @param string $xml
   * @return mixed $faultstring if valid xml is given, NULL if not
   */
  public static function parseForFaultString($xml) {
    $dom = new DOMDocument();
    if (@$dom->loadXML($xml)) {
      $xpath = new DOMXPath($dom);
    }
    else {
      return NULL;
    }

    $query = '//faultstring';
    $nodelist = $xpath->query($query);
    if ( $nodelist->length < 1 ) {
      return NULL;
    }
    return $nodelist->item(0)->nodeValue;
  }

  // this method needs to called from outside scope.. make it public
  public static function getValue($object) {
    if (is_array($object)) {
      //array not allowed
      throw new TingClientException('Unexpected object array in getValue');
    }
    else {
      return self::getBadgerFishValue($object, '$');
    }
  }

  protected static function getAttributeValue($object, $attributeName) {
    $attribute = self::getAttribute($object, $attributeName);
    if (is_array($attribute)) {
      //array not allowed
      throw new TingClientException('Unexpected object array in getAttributeValue');
    }
    else {
      return self::getValue($attribute);
    }
  }

  protected static function getAttribute($object, $attributeName) {
    //ensure that attribute names are prefixed with @
    $attributeName = ($attributeName[0] != '@') ? '@' . $attributeName : $attributeName;
    return self::getBadgerFishValue($object, $attributeName);
  }

  protected static function getNamespace($object) {
    return self::getBadgerFishValue($object, '@');
  }

  /**
   * Helper to reach JSON BadgerFish values with tricky attribute names.
   */
  protected static function getBadgerFishValue($badgerFishObject, $valueName) {
    $properties = get_object_vars($badgerFishObject);
    if (isset($properties[$valueName])) {
      $value = $properties[$valueName];
      if (is_string($value)) {
        //some values contain html entities - decode these
        $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
      }

      return $value;
    }
    else {
      return NULL;
    }
  }

}