<?php
  /**
   * Created by PhpStorm.
   * User: benduffin
   * Date: 02/09/15
   * Time: 20:45
   */
  class gTelnetClient {

    private $host;
    private $port;
    private $socket;
    private $client_id;


    public function __construct($host, $port, $socket) {
      $this->host      = $host;
      $this->port      = $port;
      $this->socket    = $socket;
      $this->client_id = md5($host . $port);
    }


    public function emit($data) {
      fwrite($this->socket, $data . PHP_EOL);
    }


    public function id($id = null) {
      if($id == null){
        return $this->client_id;

      }else{
        $this->client_id = $id;
      }
    }


    public function getSocket() {
      return $this->socket;
    }
  }
