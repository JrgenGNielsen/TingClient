<?php

/**
 * Class helpFunc.
 * Static functions to support xmlSchema class
 */
class helpFunc {
  /**
   * Chek if given path is an url.
   *
   * @param string $path
   *  The path to check
   *
   * @return bool
   *  Whether path is an url or not
   */
  public static function isUrl($path) {
    $elements = parse_url($path);
    if (strtolower($elements['scheme']) == 'http' || strtolower($elements['scheme']) == 'https') {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Split a string by ':' eg. ors:checkElectronicDeliveryRequest
   *
   * @param string $string
   *  The string to split
   *
   * @return string
   *  Value without the namespace part
   */
  public static function split($string) {
    if (strpos($string, ':') > 0) {
      $split = explode(':', $string);
      return $split[1];
    }
    return $string;
  }

  /**
   * Split a string by ':' eg.  ors:checkElectronicDeliveryRequest
   *
   * @param string $element
   *  The string to split
   *
   * @return bool|string
   *  Type (namespace) of the string
   */
  public static function getType($element) {
    if (strpos($element, ':') > 0) {
      $split = explode(':', $element);
      return $split[0];
    }
    return FALSE;
  }

  /**
   * Get soapheader for the request
   *
   * @param array $namespaces
   *  The namespaces in the header
   *
   * @return string
   *  Soapheader
   */
  public static function soapHeaders($namespaces) {
    $ret .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope"' . "\n";
    foreach ($namespaces as $prefix => $namespace) {
      $ret .= 'xmlns:' . $prefix . '="' . $namespace . "\"\n";
    }
// remove last \n character
    $ret = substr($ret, 0, -1);
    $ret .= '>' . "\n" . '<SOAP-ENV:Body>' . "\n";

    return $ret;
  }

  /**
   * Get a soapfooter
   *
   * @return string
   */
  public static function soapFooter() {
    $ret .= '</SOAP-ENV:Body>' . "\n";
    $ret .= '</SOAP-ENV:Envelope>' . "\n";


    return $ret;
  }
}
