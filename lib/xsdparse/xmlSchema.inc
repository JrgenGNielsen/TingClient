<?php

/**
 * Class xmlSchema.
 *
 * Load a xml schema definition and retrive element definitions.
 *
 * Sample usage; Get an array describing checkElectronicDeliveryRequest element
 * from openorder.xsd;
 *
 * $schema = new xmlSchema();
 * $schema -> getFromFile ('opeorder.xsd')
 * $definition = $schema -> getSequenceArray ('checkElectronicDeliveryRequest')
 *
 */
class xmlSchema {
  /**
   * @var DOMXPath xpath;
   */
  public $xpath;

  public function __construct() {
  }

  /**
   * Get an array describing an element
   *
   * @param $element_name ;
   *  Name of the element
   *
   * @return array | NULL
   *  Elementdefinition or NULL if element was not found
   */
  public function getSequenceArray($element_name) {
    $seq = $this->getSequence($element_name);

    foreach ($seq as $element) {
      $arr[] = $this->getElementAttributes($element);
    }

    foreach ($arr as $key => $val) {
      $ret[$val['name']] = strtoupper($val['name']);
    }

    return $ret;
  }

  /**
   * Load xsd from file.
   *
   * @param string $filename
   *
   * @throws \Exception
   */
  public function getFromFile($filename) {
    $dom = new DOMDocument();
    if (!@$dom->load($filename)) {
      throw new TingClientXmlException('bad file: ' . $filename);
    }

    $this->xpath = new DOMXPath($dom);
  }

  /**
   * Load xsd from url.
   *
   * @param string $filename
   */
  public function getFromUrl($filename) {
    $dom = new DOMDocument();
    if (!@$dom->load($filename)) {
      throw new TingClientXmlException('bad file: ' . $filename);
    }
    $this->xpath = new DOMXPath($dom);
  }

  /**
   * Initialize from xml.
   *
   * @param string $xml
   */
  public function setFromXml($xml) {
    $dom = new DOMDocument();
    if (!@$dom->loadXML($xml)) {
      throw new TingClientXmlException('bad xml: ' . $xml);
    }
    $this->xpath = new DOMXPath($dom);
  }

  /**
   * Get attributes from given element name.
   *
   * @param $element_name
   *
   * @return mixed
   */
  public function getElementAttributes($element_name) {
    $element = helpFunc::split($element_name);

    $query = "//*[@name='" . $element . "']/@*";
    $nodes = $this->xpath->query($query);
    $ret['wsdlname'] = $element_name;
    foreach ($nodes as $node) {
      $ret[$node->nodeName] = $node->nodeValue;
    }

    return $ret;
  }

  /**
   * Check if given element is simpleType
   *
   * @param $element_name
   *
   * @return bool
   */
  public function isSimpleType($element_name) {
    $query = "//*[@name='" . $element_name . "']/@*";
    $nodes = $this->xpath->query($query);
    foreach ($nodes as $node) {
      if ($node->nodeName == 'type') {
        $type = $node->nodeValue;
        break;
      }
    }

    if (!$type) {
      //check if simpleType is inline
      $query = "//*[@name='" . $element_name . "']/*[local-name()='simpleType']";
      $nodes = $this->xpath->query($query);
      if ($nodes->length > 0) {
        return TRUE;
      }

      return FALSE;
    }

    $typename = helpFunc::split($type);
    $query = "//*[local-name()='simpleType'][@name='" . $typename . "']";

    $nodes = $this->xpath->query($query);
    if ($nodes->length > 0) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Get sequence for given element.
   *
   * @param $element_name
   *  Name of the sequence
   *
   * @return array | Null
   *  Sequence definition or NULL if not found
   */
  public function getSequence($element_name) {
    $element = helpFunc::split($element_name);

    $query = "//*[@name='" . $element . "']//*[local-name()='element']/@*";
    $nodes = $this->xpath->query($query);
    foreach ($nodes as $node) {
      if ($node->nodeName == "ref" || $node->nodeName == "type") {
        $ret[] = $node->nodeValue;
      }
    }

    return $ret;
  }

  /**
   * Get all namespaces.
   *
   * @return array
   *  Namespaces in a array
   */
  public function namespaces() {
    // get all namespaces
    $query = "//namespace::*";
    $nodelist = $this->xpath->query($query);

    $namespaces = array();
    foreach ($nodelist as $node) {
      // remove 'xlmns:'
      if ($index = strpos($node->nodeName, ':')) {
        $key = substr($node->nodeName, $index + 1);
      }
      else {
        $key = $node->nodeName;
      }

      $namespaces[$key] = $node->nodeValue;
    }
    return $namespaces;
  }
}

