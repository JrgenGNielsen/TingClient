<?php

/**
 * @file
 * Dummy logger which does nothing
 */
class TingClientVoidLogger extends TingClientLogger {
  /**
   * Do the actual logging
   * @param $message
   * @param $variables
   * @param $severity
   */
  protected function doLog($message, $variables, $severity) {
    print $message;
  }
}

