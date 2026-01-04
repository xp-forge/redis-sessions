<?php namespace web\session\redis;

use web\session\{Persistence, SessionInvalid};

class Session extends Persistence {
  private $protocol, $id;

  /**
   * Creates a new file-based session
   *
   * @param  web.session.Sessions $sessions
   * @param  io.redis.RedisProtocol $protocol
   * @param  int $id
   * @param  int $expires
   * @param  bool $detached
   */
  public function __construct($sessions, $protocol, $id, $expires, $detached= false) {
    parent::__construct($sessions, $detached, $expires);
    $this->protocol= $protocol;
    $this->id= $id;
  }

  /** @return string */
  public function id() { return $this->id; }

  /** @return void */
  public function destroy() {
    $this->expires= time() - 1;
    $this->detached= false;
    $this->protocol->command('DEL', 'session:'.$this->id);
  }

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys() {
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }
    $r= [];
    foreach ($this->protocol->command('HKEYS', 'session:'.$this->id) as $key) {
      '_' === $key || $r[]= $key;
    }
    return $kr;
  }

  /**
   * Registers a value - writing it to the session
   *
   * @param  string $name
   * @param  var $value
   * @return void
   * @throws web.session.SessionInvalid
   */
  public function register($name, $value) {
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }
    $this->protocol->command('HSET', 'session:'.$this->id, $name, json_encode($value));
  }

  /**
   * Retrieves a value - reading it from the session
   *
   * @param  string $name
   * @param  var $default
   * @return var
   * @throws web.session.SessionInvalid
   */
  public function value($name, $default= null) {
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }
    $value= $this->protocol->command('HGET', 'session:'.$this->id, $name);
    return null === $value ? $default : json_decode($value, true);
  }

  /**
   * Removes a value - deleting it from the session
   *
   * @param  string $name
   * @return bool
   * @throws web.session.SessionInvalid
   */
  public function remove($name) {
    if (time() >= $this->expires) {
      throw new SessionInvalid($this->id);
    }
    $this->protocol->command('HDEL', 'session:'.$this->id, $name);
  }
}