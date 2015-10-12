<?php
interface ITingClientRequestAdapterInterface{
  public function execute(TingClientRequest $request);
}