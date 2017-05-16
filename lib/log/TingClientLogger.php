<?php

/**
 * @file
 * Base logger class which can be injected into the Ting client.
 */
abstract class TingClientLogger {

  const EMERGENCY = 'EMERGENCY';
  const ALERT = 'ALERT';
  const CRITICAL = 'CRITICAL';
  const ERROR = 'ERROR';
  const WARNING = 'WARNING';
  const NOTICE = 'NOTICE';
  const INFO = 'INFO';
  const DEBUG = 'DEBUG';

  static public $levels = array(
    self::EMERGENCY,
    self::ALERT,
    self::CRITICAL,
    self::ERROR,
    self::WARNING,
    self::NOTICE,
    self::INFO,
    self::DEBUG
  );

  public $log_time = 0;

  /**
   * Log a message.
   *
   * @param string $message The message to log
   * @param array $variables
   * @param string $severity The severity of the message
   *
   */
  public function log($message, $variables, $severity = self::INFO) {
    if (!in_array($severity, self::$levels)) {
      throw new \TingClientException('Unsupported severity: ' . $severity);
    }
    $this->doLog($message, $variables, $severity);
  }

  /**
   * Log start time
   */
  public function startTime() {
    $time = explode(' ', microtime());
    $this->log_time = -($time[1] + $time[0]);
  }

  /**
   * Log stop time
   */
  public function stopTime() {
    $time = explode(' ', microtime());
    $this->log_time += $time[1] + $time[0];
  }

  abstract protected function doLog($message, $variables, $severity);
}

