<?php namespace web\session;

use util\Random;
use web\session\redis\RedisProtocol;
use web\session\redis\Session;

/**
 * Session factory connecting to a Redis server
 *
 * @see   https://redis.io/
 */
class Redis extends Sessions {
  private $protocol, $rand;

  /**
   * Creates a new Redis session
   *
   * @param string|util.URI $dsn
   */
  public function __construct($dsn) {
    $this->protocol= new RedisProtocol($dsn);
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
    return new Session($this, $this->protocol, $id, true);
  }

  /**
   * Opens an existing and valid session. 
   *
   * @param  string $id
   * @return web.session.ISession
   */
  public function open($id) {
    if ($this->protocol->command('EXISTS', 'session:'.$id)) {
      return new Session($this, $this->protocol, $id);
    }
    return null;
  }
}