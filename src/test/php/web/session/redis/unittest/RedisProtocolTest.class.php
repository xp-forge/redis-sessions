<?php namespace web\session\redis\unittest;

use peer\AuthenticationException;
use peer\Socket;
use unittest\TestCase;
use web\session\redis\RedisProtocol;

class RedisProtocolTest extends TestCase {

  #[@test]
  public function can_create() {
    new RedisProtocol('redis://localhost');
  }

  #[@test]
  public function can_create_with_auth() {
    new RedisProtocol('redis://secret@localhost');
  }

  #[@test]
  public function connect() {
    $io= new Channel();

    $fixture= new RedisProtocol($io);
    $fixture->connect();

    $this->assertTrue($io->connected);
  }

  #[@test]
  public function initially_not_connected() {
    $io= new Channel();

    $fixture= new RedisProtocol($io);

    $this->assertFalse($io->connected);
  }

  #[@test]
  public function automatically_connects_if_necessary() {
    $io= new Channel("+OK\r\n");

    $fixture= new RedisProtocol($io);
    $fixture->send('ECHO', 'test');

    $this->assertTrue($io->connected);
  }

  #[@test]
  public function authenticate() {
    $io= new Channel("+OK\r\n");

    $fixture= new RedisProtocol($io, 'password');
    $fixture->connect();

    $this->assertTrue($io->connected);
  }

  #[@test]
  public function authentication_failure() {
    $io= new Channel("-ERR password incorrect\r\n");

    $fixture= new RedisProtocol($io, 'password');
    try {
      $fixture->connect();
      $this->fail('No exception raised', null, AuthenticationException::class);
    } catch (AuthenticationException $expected) {
      // OK
    }
    $this->assertFalse($io->connected);
  }

  #[@test]
  public function set() {
    $io= new Channel("+OK\r\n");

    $fixture= new RedisProtocol($io);
    $fixture->connect();

    $result= $fixture->send('SET', 'key', 'value');
    $this->assertEquals("*3\r\n\$3\r\nSET\r\n\$3\r\nkey\r\n\$5\r\nvalue\r\n", $io->out);
    $this->assertEquals('OK', $result);
  }

  #[@test]
  public function exists() {
    $io= new Channel(":1\r\n");

    $fixture= new RedisProtocol($io);
    $fixture->connect();

    $result= $fixture->send('EXISTS', 'key');
    $this->assertEquals("*2\r\n\$6\r\nEXISTS\r\n\$3\r\nkey\r\n", $io->out);
    $this->assertEquals(1, $result);
  }

  #[@test]
  public function get_non_existant() {
    $io= new Channel("\$-1\r\n");

    $fixture= new RedisProtocol($io);
    $fixture->connect();

    $result= $fixture->send('GET', 'key');
    $this->assertEquals("*2\r\n\$3\r\nGET\r\n\$3\r\nkey\r\n", $io->out);
    $this->assertEquals(null, $result);
  }

  #[@test]
  public function get() {
    $io= new Channel("\$5\r\nvalue\r\n");

    $fixture= new RedisProtocol($io);
    $fixture->connect();

    $result= $fixture->send('GET', 'key');
    $this->assertEquals("*2\r\n\$3\r\nGET\r\n\$3\r\nkey\r\n", $io->out);
    $this->assertEquals('value', $result);
  }

  #[@test]
  public function keys() {
    $io= new Channel("*2\r\n\$3\r\nkey\r\n\$5\r\ncolor\r\n");

    $fixture= new RedisProtocol($io);
    $fixture->connect();

    $result= $fixture->send('KEYS', '*');
    $this->assertEquals("*2\r\n\$4\r\nKEYS\r\n\$1\r\n*\r\n", $io->out);
    $this->assertEquals(['key', 'color'], $result);
  }
}