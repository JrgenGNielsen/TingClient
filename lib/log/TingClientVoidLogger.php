<?php

/**
 * Dummy logger which does nothing
 */
class TingClientVoidLogger extends TingClientLogger {
  protected function doLog($message, $variables, $severity) {
    print $message;
  }
}

