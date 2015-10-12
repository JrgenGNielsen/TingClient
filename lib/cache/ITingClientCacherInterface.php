<?php
interface ITingClientCacherInterface{
  function set($key, $value);
  function get($key);
  function clear();
}