<?php
/**
 * @file Register autoload (placement) of classes in TingClient
 */

spl_autoload_register(function ($class_name) {
// define locations of classes
  $path = __DIR__;
  $dirs = array(
    $path . '/cache/',
    $path . '/request/',
    $path . '/exception/',
    $path . '/adapter/',
    $path . '/log/',
    $path . '/result/',
    $path . '/soapClient/',
    $path . '/xsdparse/',
    $path . '/common/',
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
