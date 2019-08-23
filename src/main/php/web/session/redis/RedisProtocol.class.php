<?php namespace web\session\redis;

use lang\Closeable;
use peer\AuthenticationException;
use peer\ProtocolException;
use peer\Socket;
use util\Secret;
use util\URI;

/**
 * Redis protocol implementation
 *
 * @see   https://redis.io/topics/protocol
 * @test  xp://web.session.redis.unittest.RedisProtocolTest
 */
class RedisProtocol implements Closeable {
  private $conn, $auth;

  /**
   * Creates a new protocol instance
   *
   * @param  string|util.URI|peer.Socket $conn
   * @param  ?string|?util.Secret $auth
   */
  public function __construct($conn, $auth= null) {
    if ($conn instanceof Socket) {
      $this->conn= $conn;
      $this->auth= null === $auth ? null : ($auth instanceof Secret ? $auth : new Secret($auth));
    } else {
      $uri= $conn instanceof URI ? $conn : new URI($conn);
      $this->conn= new Socket($uri->host(), $uri->port() ?: 6379);
      $this->auth= null === $auth ? new Secret($uri->user()) : ($auth instanceof Secret ? $auth : new Secret($auth));
    }
  }

  /**
   * Connect and authenticate, if necessary
   *
   * @return  self
   * @throws  peer.ConnectException
   * @throws  peer.AuthenticationException
   */
  public function connect() {
    $this->conn->connect();

    // Do not use send() and read() to prevent auth from leaking into stacktraces
    if (null !== $this->auth) {
      $pass= $this->auth->reveal();
      $this->conn->write(sprintf("*2\r\n\$4\r\nAUTH\r\n\$%d\r\n%s\r\n", strlen($pass), $pass));
      $r= $this->conn->readLine();
      if ('+OK' !== $r) {
        $this->conn->close();
        throw new AuthenticationException($r, $this->auth);
      }
    }

    return $this;
  }

  /**
   * Reads response
   *
   * @return var 
   * @throws peer.ProtocolException
   */
  private function read() {
    $r= $this->conn->readLine();
    // DEBUG \util\cmd\Console::writeLine('<<< ', addcslashes($r, "\r\n"));

    switch ($r[0]) {
      case ':': // integers
        return (int)substr($r, 1);

      case '+': // simple strings
        return substr($r, 1);

      case '$': // bulk strings
        if (-1 === ($l= (int)substr($r, 1))) return null;
        $r= '';
        do {
          $r.= $this->conn->readBinary(min(8192, $l - strlen($r)));
        } while (strlen($r) < $l && !$this->conn->eof());
        $this->conn->readBinary(2);
        return $r;

      case '*': // arrays
        if (-1 === ($l= (int)substr($r, 1))) return null;
        $r= [];
        for ($i= 0; $i < $l; $i++) {
          $r[]= $this->read();
        }
        return $r;

      case '-': // errors
        throw new ProtocolException(substr($r, 1));
    }
  }

  /**
   * Sends request and reads response
   *
   * @param  var... $args
   * @return var 
   * @throws peer.ProtocolException
   */
  public function send(... $args) {
    $this->conn->isConnected() || $this->connect();

    $s= '*'.sizeof($args)."\r\n";
    foreach ($args as $arg) {
      $s.= '$'.strlen($arg)."\r\n".$arg."\r\n";
    }

    // DEBUG \util\cmd\Console::writeLine('>>> ', addcslashes($s, "\r\n"));
    $this->conn->write($s);
    return $this->read();
  }

  /** @return void */
  public function close() {
    if ($this->conn->isConnected()) {
      $this->conn->close();
    }
  }

  /** @return void */
  public function __destruct() {
    $this->close();
  }
}