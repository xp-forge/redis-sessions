<?php namespace web\session;

use io\redis\RedisProtocol;
use util\Random;
use web\session\redis\Session;

/**
 * Session factory connecting to a Redis server
 *
 * @see   https://redis.io/
 * @test  web.session.redis.unittest.RedisTest
 */
class InRedis extends Sessions {
  private $protocol, $rand;

  /**
   * Creates a new Redis session
   *
   * @param string|util.URI|io.redis.RedisProtocol $conn
   */
  public function __construct($conn) {
    $this->protocol= $conn instanceof RedisProtocol ? $conn : new RedisProtocol($conn);
    $this->rand= new Random();
  }

  /**
   * Creates a session
   *
   * @return web.session.ISession
   */
  public function create() {
    $id= bin2hex($this->rand->bytes(20));

    // Redis doesn't store empty sets or hashes (and will delete empty sets and hashes)
    // Prevent this by adding a placeholder value.
    $this->protocol->command('HSET', 'session:'.$id, '_', '.keep');
    $this->protocol->command('EXPIRE', 'session:'.$id, $this->duration);
    return new Session($this, $this->protocol, $id, time() + $this->duration, true);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return web.session.ISession
   */
  public function open($id) {

    // TTL of nonexistant values will be -2
    $ttl= $this->protocol->command('TTL', 'session:'.$id);
    if ($ttl >= 0) {
      return new Session($this, $this->protocol, $id, time() + $ttl);
    }
    return null;
  }
}