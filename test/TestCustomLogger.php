<?php
class TestCustomLogger extends TingClientLogger {
  protected function doLog($message, $variables, $severity, $raw_entry) {
    print 'LOGGING';
  }
}
