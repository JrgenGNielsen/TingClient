<?php

/**
 * @file
 * Class TingClientParameterMap
 * Generic mapping of data from Open Format Response.
 *
 * Mapping:
 *   protected $mapping = array(
 *     'hitCount' => 'numTotalObjects',
 *     // This is mapping of string e.g. ResponseObject->hitCount->{'$'} will be mapped to $this->numTotalObjects
 *
 *     'more' => 'more',
 *     // This is mapping of boolean e.g. ResponseObject->more->{'$'}(boolean) will be mapped to $this->more
 *
 *     'searchResult' => 'results'
 *     ),
 *     // This is mapping of array e.g. ResponseObject->searchResult array will be mapped to $this->results array
 *
 *   );
 */
class TingClientParameterMap {

  static $mapping;
  /**
   * Map searchresponse according to member $mapping
   * @param array $mapping
   *  key=>value array of terms to map eg. ['searchResult' => 'results']
   * @param \stdClass $response
   *  response IN JSON from opensearch
   * @return \stdClass
   *  mapped values
   */
  public static function mapResult($response, $mapping) {
    self::$mapping = $mapping;
    $map = array();
    self::mapMe($response, $map);
    return self::flat_array2object($map);
  }

  /**
   * Convert a FLAT array to stdClass.
   * NOTICE array can NOT be nested.
   * @param array $map
   * @return \stdClass
   */
  private static function flat_array2object(array $map) {
    $object = new \stdClass();
    foreach ($map as $key => $value) {
      $object->$key = $value;
    }
    return $object;
  }

  /**
   * Run throgh given object and place found parameter in given map..
   *
   * @param array|object $obj ; The object/array to iterate
   * @param array $map ; Where to put the values found
   */
  private static function mapMe($obj, &$map) {
    foreach ($obj as $key => $value) {
      if (array_key_exists($key, self::$mapping)) {
        $map[self::$mapping[$key]] = self::getMapValue($value);
      } else {
        if (is_object($value) || is_array($value)) {
          // Recursive
          self::mapMe($value, $map);
        }
      }
    }
  }

  /**
   * Get a value from a parmater
   * @param mixed $param
   * @return mixed string
   */
  private static function getMapValue($param) {
    if (is_array($param)) {
      return $param;
    }
    if (is_object($param)) {
      return isset($param->{'$'}) ? $param->{'$'} : '';
    } else {
      return $param;
    }
  }
}
