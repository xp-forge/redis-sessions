<?php namespace web\session\redis;

use web\session\ISession;
use web\session\SessionInvalid;

class Session implements ISession {
  private $sessions, $protocol, $id, $new;

  /**
   * Creates a new file-based session
   *
   * @param  web.session.Sessions $sessions
   * @param  web.session.redis.RedisProtocol $protocol
   * @param  int $id
   * @param  bool $new
   */
  public function __construct($sessions, $protocol, $id, $new= false) {
    $this->sessions= $sessions;
    $this->protocol= $protocol;
    $this->id= $id;
    $this->new= $new;
  }

  /** @return string */
  public function id() { return $this->id; }

  /** @return bool */
  public function valid() {
    return $this->protocol->send('TTL', 'session:'.$this->id) > 0;
  }

  /** @return void */
  public function destroy() {
    $this->protocol->send('DEL', 'session:'.$this->id);
  }

  /**
   * Returns all session keys
   *
   * @return string[]
   */
  public function keys() {
    $r= [];
    foreach ($this->protocol->send('HKEYS', 'session:'.$this->id) as $key) {
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
    $this->protocol->send('HSET', 'session:'.$this->id, $name, json_encode($value));
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
    $value= $this->protocol->send('HGET', 'session:'.$this->id, $name);
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
    $this->protocol->send('HDEL', 'session:'.$this->id, $name);
  }

  /**
   * Closes this session
   *
   * @return void
   */
  public function close() {
    // NOOP
  }

  /**
   * Transmits this session to the response
   *
   * @param  web.Response $response
   * @return void
   */
  public function transmit($response) {
    if ($this->new) {
      $this->sessions->attach($this, $response);
      $this->new= false;
    } else if ($this->protocol->send('TTL', 'session:'.$this->id) <= 0) {
      $this->sessions->detach($this, $response);
    }
  }
}