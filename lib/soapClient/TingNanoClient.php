<?php
class TingNanoClient extends NanoSOAPClient implements ITingSoapClientInterface{
  public function __construct($endpoint, $options = array()){
    parent::__construct($endpoint, $options);
  }
}