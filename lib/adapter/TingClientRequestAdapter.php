<?php

class TingClientRequestAdapter implements ITingClientRequestAdapterInterface{
  private $client;
  private $logger;
  private $cacher;

  public function __construct(ITingSoapClientInterface $client) {
    $this->logger = new TingClientVoidLogger();
    $this->client = $client;
  }

  public function execute(TingClientRequest $request){
    $action = $request->getParameter('action');
    $request->unsetParameter('action');
    $params = $request->getParameters();
    $response = $this->client->call($action, $params);

    return $response;
  }

  public function setLogger($logger){
    $this->logger = $logger;
  }

  public function setCacher(ITingClientCacherInterface $cacher){
    $this->cacher = $cacher;
  }
}