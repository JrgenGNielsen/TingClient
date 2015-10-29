<?php

/**
 * @file Class TingClientRequestInterface
 * Base class for requests. Extending methods must implement remaainder methods
 * of TingClientRequestCacheInterface and abstract method processResponse
 */
abstract class TingClientRequest implements TingClientRequestCacheInterface {

  /* suffixes to use for cache variables */
  const CACHELIFETIME = '_cache_lifetime';
  const CACHEENABLE = '_cache_enable';

  // for tracing the request
  private $trackingId;

  /**
   * @var string
   */
  protected $cacheKey;
  /**
   * @var string
   */
  private $nameSpace;
  /**
   * @var array
   */
  private $xsdNameSpace;
  /**
   * @var string
   */
  private $wsdlUrl;
  /**
   * @var array
   */
  protected $parameters = array();

  /**
   * Abstract function to be implemented by extending classes
   * @param \stdClass $response
   *
   * @return mixed
   */
  abstract public function processResponse(stdClass $response);

  public function __construct($wsdlUrl, $serviceName = NULL) {
    $this->wsdlUrl = $wsdlUrl;
  }

  /**
   * Make a cachekey based on request parameters
   *
   * @param array  $params
   * @param string $ret
   **/
  private function make_cache_key($params, $ret = '') {
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

  /**
   * Set xsdNameSpace
   *
   * @param array $value
   */
  public function setXsdNameSpace(array $value) {
    $this->xsdNameSpace = $value;
  }

  /**
   * Get xsdNameSpace
   *
   * @return bool|array
   */
  public function getXsdNameSpace() {
    return !empty($this->xsdNameSpace) ? $this->xsdNameSpace : FALSE;
  }


  /**
   * Get ClientType
   *
   * @return string
   *
   */
  public function getClientType() {
    return 'NANO';
  }

  /**
   * Check response. Defaults to true. To be overridden en extending classes
   *
   * @param $response
   *
   * @return bool
   */
  public function checkResponse($response) {
    return TRUE;
  }

  /**
   * Get cachebin
   * default implementation of TingClientRequestCacheInterface::cacheBin
   * extending request can implement this method if it wishes it's own bin
   *
   * @return string
   */
  public function cacheBin() {
    return 'cache_bibdk_webservices';
  }


  /**
   * Default Implementation of TingClientRequestCacheInterface::cacheKey
   *
   * @return string
   **/
  public function cacheKey() {
    $params = $this->getParameters();
    $ret = $this->make_cache_key($params);
    return md5($ret);
  }

  /**
   * Get wsdlUrl
   *
   * @return \wsdlUrl
   */
  public function getWsdlUrl() {
    return $this->wsdlUrl;
  }

  /**
   * Get classname
   *
   * @return string
   */
  public function getClassname() {
    return get_class($this);
  }

  /**
   * Set a parameter
   *
   * @param string $name
   * @param mixed $value
   */
  public function setParameter($name, $value) {
    $this->parameters[$name] = $value;
  }

  /**
   * Unset a parameter
   *
   * @param string $name
   */
  public function unsetParameter($name) {
    if (isset($this->parameters[$name])) {
      unset($this->parameters[$name]);
    }
  }

  /**
   * Get a parameter
   *
   * @param string $name
   *
   * @return mixed
   */
  public function getParameter($name) {
    return $this->parameters[$name];
  }

  /**
   * Set all parameters
   *
   * @param array $array
   */
  public function setParameters($array) {
    $this->parameters = $array;
  }

  /**
   * Get all parameters
   *
   * @return array
   */
  public function getParameters() {
    return $this->parameters;
  }


  /**
   * Check if response can be decoded
   *
   * @param string $responseString
   *
   * @return mixed
   * @throws \TingClientException
   */
  public function parseResponse($responseString) {
    if ($this->parameters['outputType'] != 'json') {
      return $responseString;
    }

    // Here the output type should be json unless its an error.
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
      throw new TingClientException('Unexpected JSON response: ' . var_export($response, TRUE));
    }

    return $this->processResponse($response);
  }

  /**
   * Response from webservice is ALWAYS xml if validation fails
   * elemants <faultCode> and <faultString> will be present in that case
   *
   * @param string $xml
   *
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
    if ($nodelist->length < 1) {
      return NULL;
    }
    return $nodelist->item(0)->nodeValue;
  }

  /**
   * Get value of stdObject
   *
   * @param stdClass $object
   *
   * @return null|string
   * @throws \TingClientException
   */
  public static function getValue($object) {
    if (is_array($object)) {
      //array not allowed
      throw new TingClientException('Unexpected object array in getValue');
    }
    else {
      return self::getBadgerFishValue($object, '$');
    }
  }

  /**
   * Get value of attribute
   *
   * @param stdClass $object
   * @param string $attributeName
   *
   * @return null|string
   * @throws \TingClientException
   */
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

  /**
   * Get attribute from given object and attribute name
   *
   * @param stdClass $object
   * @param string $attributeName
   *
   * @return null|string
   */
  protected static function getAttribute($object, $attributeName) {
    //ensure that attribute names are prefixed with @
    $attributeName = ($attributeName[0] != '@') ? '@' . $attributeName : $attributeName;
    return self::getBadgerFishValue($object, $attributeName);
  }

  /**
   * Get namespace
   * @param $object
   *
   * @return null|string
   */
  protected static function getNamespace($object) {
    return self::getBadgerFishValue($object, '@');
  }

  /**
   * Get value from given valueName
   *
   * @param stdClass $badgerFishObject
   * @param string $valueName
   *
   * @return null|string
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
