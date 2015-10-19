<?php
class TingNanoClient extends NanoSOAPClient implements TingSoapClientInterface{
  public function __construct($endpoint, $options = array()){
    parent::__construct($endpoint, $options);
  }
}