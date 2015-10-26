<?php

/**
 * @file Class TingClientCommon
 * Static functions to be used in various classes
 */
class TingClientCommon {
  /**
   * Check if given string is valid xml.
   *
   * @param string $xml
   *  The xml to validate
   *
   * @return bool
   *  Whether string is valid xml or not
   */
  public static function validateXml($xml) {
    $dom = new DOMDocument();
    if (@$dom->loadXML($xml)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Convert an url to a filename [http://openadhl.addi.dk/1.1/adhl.xsd -> openadhl-addi-dk-1-1-adhl-xsd]
   *
   * @param string $url
   *
   * @return string
   */
  public static function urlToFilename($url) {
    $parts = parse_url($url);
    // do not use the protocol (http) in filename
    unset($parts['scheme']);
    $path = implode('_', $parts);
    $search = array('.', '/', '_');
    $replace = array('-', '-');
    return str_replace($search, $replace, $path);
  }


  /**
   * Check if xsd_url is set. If not get it from given url and store it in tmp dir for later use
   *
   * @param array $xsd_url
   * @param array $params
   *
   * @TODO cleanup old files
   *
   * */
  public static function checkXsd($xsd_url, array $params) {
    $filename = self::urlToFilename($xsd_url);
    // check in temp dir for the xsd.
    $dir = sys_get_temp_dir();
    $path = $dir . '/' . $filename;

    if (!file_exists($path)) {
      if (!self::isUrl($xsd_url)) {
        return $params;
      }
      // get and store file in temp dir
      $file = file_get_contents($xsd_url);
      // only store valid xsd files
      if (self::validateXml($file)) {
        file_put_contents($dir . '/' . $filename, $file);
      }
      else {
        return $params;
      }
    }

    return self::validateXsd($path, $params);
  }

  /**
   * Check if given string is an url
   *
   * @param string
   *
   * @return bool
   */
  public static function isUrl($string) {
    if (filter_var($string, FILTER_VALIDATE_URL)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get the sequence given in params['action'] from schemadefinition.
   *
   * Validate (rearrange) given other parameters to follow the order given in
   * the sequence
   *
   * @param string $path
   * @param array $params
   *
   * @return array
   * @throws \Exception
   */
  private static function validateXsd($path, $params) {
    $schema = new xmlSchema();
    try {
      $schema->getFromFile($path);
    }
    catch(TingClientXmlException $e){
      return $params;
    }

    $seq = $schema->getSequence($params['action']);
    $arr[] = 'action';
    foreach ($seq as $element) {
      $s = $schema->getElementAttributes($element);
      $arr[] = $s['name'];
    }

    return self::checkParameters($arr, $params);
  }


  /**
   * Rearrange parameters.
   *
   * @param array $real_params
   *  parameters from schemadefinition
   * @param array $params
   *  parameter from the request
   *
   * @return array
   *  parameters in the correct order
   */
  private static function checkParameters($real_params, $params) {
    $parsed_params = array();
    foreach ($real_params as $real_param) {
      if (!empty($params[$real_param])) {
        $parsed_params[$real_param] = $params[$real_param];
      }
    }

    return $parsed_params;
  }
}