<?php namespace web\session\redis\unittest;

use io\redis\RedisProtocol;
use unittest\{Assert, Expect, Test, TestCase};
use web\session\{ISession, InRedis, SessionInvalid};

class RedisTest {

  #[Test]
  public function create_session() {
    $io= new Channel("+OK\r\n:1\r\n");
    $fixture= new InRedis(new RedisProtocol($io));

    $session= $fixture->create();
    $id= $session->id();
    Assert::equals(
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

  #[Test]
  public function open_session() {
    $io= new Channel(":86300\r\n");
    $fixture= new InRedis(new RedisProtocol($io));

    $session= $fixture->open('test');
    Assert::equals("*2\r\n\$3\r\nTTL\r\n\$12\r\nsession:test\r\n", $io->out);
    Assert::instance(ISession::class, $session);
  }

  #[Test]
  public function open_expired_session() {
    $io= new Channel(":-2\r\n");
    $fixture= new InRedis(new RedisProtocol($io));

    $session= $fixture->open('test');
    Assert::equals("*2\r\n\$3\r\nTTL\r\n\$12\r\nsession:test\r\n", $io->out);
    Assert::null($session);
  }

  #[Test]
  public function value() {
    $io= new Channel(":86300\r\n\$7\r\n\"value\"\r\n");
    $fixture= new InRedis(new RedisProtocol($io));

    Assert::equals('value', $fixture->open('test')->value('value'));
  }

  #[Test, Expect(SessionInvalid::class)]
  public function invalid_session() {
    $io= new Channel(":0\r\n");
    $fixture= new InRedis(new RedisProtocol($io));

    $fixture->open('test')->value('value');
  }
}