<?php namespace web\session\redis\unittest;

use unittest\TestCase;
use web\session\redis\RedisProtocol;

class RedisProtocolTest extends TestCase {

  #[@test]
  public function can_create() {
    new RedisProtocol('redis://localhost');
  }
}