<?php namespace web\session\redis\unittest;

use peer\Socket;
use peer\SocketException;

class Channel extends Socket {
  public $connected= false;
  public $in= '', $out;

  public function __construct($in= '') {
    $this->in= $in;
    $this->out= '';
  }

  public function connect($timeout= 2) {
    $this->connected= true;
  }

  public function readLine($maxLen= 4096) {
    if (!$this->connected) {
      throw new SocketException('Read of '.$maxLen.' bytes failed: Connection closed');
    }

    $p= strpos($this->in, "\n");
    if (false === $p) {
      $chunk= $this->in;
      $this->in= null;
    } else {
      $chunk= rtrim(substr($this->in, 0, $p), "\r");
      $this->in= substr($this->in, $p + 1);
    }
    return $chunk;
  }

  public function readBinary($maxLen= 4096) {
    if (!$this->connected) {
      throw new SocketException('Read of '.$maxLen.' bytes failed: Connection closed');
    }

    $chunk= substr($this->in, 0, $maxLen);
    if ($maxLen >= strlen($this->in)) {
      $this->in= null;
    } else {
      $this->in= substr($this->in, $maxLen);
    }
    return $chunk;
  }

  public function eof() {
    return null === $this->in;
  }

  public function write($chunk) {
    if (!$this->connected) {
      throw new SocketException('Write of '.strlen($chunk).' bytes to socket failed: Connection closed');
    }

    $this->out.= $chunk;
  }

  public function close() {
    $this->connected= false;
  }
}