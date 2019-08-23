<?php namespace web\session\redis\unittest;

use unittest\TestCase;
use web\session\ISession;
use web\session\Redis;
use web\session\SessionInvalid;
use web\session\redis\RedisProtocol;

class RedisTest extends TestCase {

  #[@test]
  public function create_session() {
    $io= new Channel("+OK\r\n:1\r\n");
    $fixture= new Redis(new RedisProtocol($io));

    $session= $fixture->create();
    $id= $session->id();
    $this->assertEquals(
      sprintf(
        "*4\r\n\$4\r\nHSET\r\n\$%1\$d\r\nsession:%2\$s\r\n\$1\r\n_\r\n\$5\r\n.keep\r\n".
        "*3\r\n\$6\r\nEXPIRE\r\n\$%1\$d\r\nsession:%2\$s\r\n\$5\r\n%3\$d\r\n",
        strlen('session:') + strlen($id),
        $id,
        $fixture->duration()
      ),
      $io->out
    );
  }

  #[@test]
  public function open_session() {
    $io= new Channel(":86300\r\n");
    $fixture= new Redis(new RedisProtocol($io));

    $session= $fixture->open('test');
    $this->assertEquals("*2\r\n\$3\r\nTTL\r\n\$12\r\nsession:test\r\n", $io->out);
    $this->assertInstanceOf(ISession::class, $session);
  }

  #[@test]
  public function open_expired_session() {
    $io= new Channel(":-2\r\n");
    $fixture= new Redis(new RedisProtocol($io));

    $session= $fixture->open('test');
    $this->assertEquals("*2\r\n\$3\r\nTTL\r\n\$12\r\nsession:test\r\n", $io->out);
    $this->assertNull($session);
  }

  #[@test]
  public function value() {
    $io= new Channel(":86300\r\n\$7\r\n\"value\"\r\n");
    $fixture= new Redis(new RedisProtocol($io));

    $this->assertEquals('value', $fixture->open('test')->value('value'));
  }

  #[@test, @expect(SessionInvalid::class)]
  public function invalid_session() {
    $io= new Channel(":0\r\n");
    $fixture= new Redis(new RedisProtocol($io));

    $fixture->open('test')->value('value');
  }
}