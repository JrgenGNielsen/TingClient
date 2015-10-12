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
   * @return bool
   *  Whether string is valid xml or not
   */
  public static function validate_xml($xml) {
    $dom = new DOMDocument();
    if (@$dom->loadXML($xml)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Convert an url to a filename [http://openadhl.addi.dk/1.1/adhl.xsd -> openadhl-addi-dk-1-1-adhl-xsd]
   *
   * @param $url
   * @return string
   */
  public static function url_to_filename($url) {
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
   * @param string $check_data
   *  Url to the xsd
   *
   * @TODO cleanup old files
   *
   * */
  public static function checkXsd($xsd_url, array $params) {
    $filename = self::url_to_filename($xsd_url);
    // check in temp dir for the xsd.
    $dir = sys_get_temp_dir();
    $path = $dir . '/' . $filename;
    if (!file_exists($path)) {
      // get and store file in temp dir
      $file = file_get_contents($xsd_url);
      // only store valid xsd files
      if (self::validate_xml($file)) {
        file_put_contents($dir . '/' . $filename, $file);
      }
      else{
        return $params;
      }
    }
    return self::validate_xsd($path, $params);
  }


  private static function validate_xsd($path, $params){

    //return $params;

     $schema = new xmlSchema();
     $schema->get_from_file($path);

     $seq = $schema->get_sequence($params['action']);
     $arr[] = 'action';
     foreach ($seq as $element) {
       $s = $schema->get_element_attributes($element);
       $arr[] = $s['name'];
     }

     return self::checkParameters($arr, $params);
  }
  private static function checkParameters($real_params, $params){
    $parsed_params = array();
    foreach ($real_params as $real_param) {
      if (!empty($params[$real_param])) {
        $parsed_params[$real_param] = $params[$real_param];
      }
    }
    return $parsed_params;

  }

}