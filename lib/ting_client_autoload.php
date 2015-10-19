<?php
/**
 * @file Register autoload (placement) of classes in TingClient
 */

spl_autoload_register(function ($class_name) {
// define locations of classes
  $path = dirname(__FILE__);
  print $path;

  $dirs = array(
    'ting-client/',
    'lib/',
    'lib/cache/',
    'lib/request/',
    'lib/exception/',
    'lib/log/',
    'lib/result/',
    'lib/soapClient/',
    'lib/xsdparse/',
    'lib/common/'
  );

  foreach ($dirs as $dir) {
    if (file_exists($dir . $class_name . '.php')) {
      require_once($dir . $class_name . '.php');
      return;
    }
    elseif (file_exists($dir . $class_name . '.inc')) {
      require_once($dir . $class_name . '.inc');
      return;
    }
  }
});
