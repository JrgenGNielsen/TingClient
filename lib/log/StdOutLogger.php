<?php

/**
 * @file log to stdout intende for use in mesos environment
 */


/**
 * Class VerboseLogger
 * This is a singleton class
 */
class StdOutLogger extends \TingClientLogger {
  private static $instance;
  // to log to stdout we need apache to run in foreground - that is what
  // we do on Kubernetes.
  private $logfile = 'php://stdout';
  private $date_format = 'H:i:s-d/m/y';

  /**
   * Get instance of this class
   * @return StdOutLogger
   */
  public static function instance() {
    if (is_null(self::$instance)) {
      self::$instance = new StdOutLogger();
    }
    return self::$instance;
  }

  /**
   * Write a log message
   * @param $message
   * @param $variables
   * @param $severity
   */
  public function doLog($message, $variables = array(),
    $severity = \TingClientLogger::DEBUG, $raw_entry) {

    if($fp = @ fopen($this->logfile, 'a')) {
      fwrite($fp, $message .  '::' . date($this->date_format) );
      fclose($fp);
    }
  }
}
