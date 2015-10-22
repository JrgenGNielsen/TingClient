<?php
class TestCustomLogger extends TingClientLogger {
  protected function doLog($message, $variables, $severity) {
    print 'LOGGING';
  }
}