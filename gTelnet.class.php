<?php
  /**
   * Created by PhpStorm.
   * User: benduffin
   * Date: 02/09/15
   * Time: 20:44
   */
  class gTelnet {

    private $host;
    private $port;
    private $server;
    private $clients = array();


    public function console($text, $eol = PHP_EOL) {
      echo $text . $eol;
    }


    public function __construct($host = '0.0.0.0', $port = '5555') {
      $this->host = $host;
      $this->port = $port;
      $address    = 'tcp://' . $this->host . ':' . $this->port;

      $this->server = stream_socket_server($address, $errno, $errorMessage);

      if($this->server === false){
        die("Could not bind to socket: $errorMessage");
      }else{
        $this->console('Listening on Port 5555 for Telnet Connection Requests ...');
      }
    }


    public function getAllSockets() {
      $tmp = array();
      foreach($this->clients as $client){
        $tmp[] = $client->getSocket();
      }

      return $tmp;
    }

    public function getClientBySocket($socket){
      foreach($this->clients as $client){
        if($socket == $client->getSocket()){
          return $client;
        }
      }

      return false;
    }

    public function dropClientBySocket($socket){
      foreach($this->clients as $cdx => $client){
        if($socket == $client->getSocket()){
          unset($this->clients[$cdx]);
          return true;
        }
      }

      return false;
    }

    public function run() {
      while(true){
        //prepare readable sockets
        $read_socks   = $this->getAllSockets();
        $read_socks[] = $this->server;

        //start reading and use a large timeout
        if(!stream_select($read_socks, $write, $except, 300000)){
          die('something went wrong while selecting');
        }

        //new client
        if(in_array($this->server, $read_socks)){
          $new_client = stream_socket_accept($this->server);

          if($new_client){
            //print remote client information, ip and port number
            $this->console('Connection accepted from ' . stream_socket_get_name($new_client, true));
            $add            = stream_socket_get_name($new_client, true);
            $parts          = explode(':', $add);
            $client_socks[] = $new_client;

            //delete the server socket from the read sockets
            unset($read_socks[array_search($this->server, $read_socks)]);
            $this->clients[] = new gTelnetClient($parts[0], $parts[1], $new_client);
            $this->console("Now there are total " . count($this->clients) . " clients");
          }

        }

        //message from existing client
        foreach($read_socks as $sock){
          $data = trim(fread($sock, 128));
          if(!$data || $data == 'exit'){
            $this->dropClientBySocket($sock);
            @fclose($sock);
            $this->console("A client disconnected. Now there are total " . count($this->clients) . " clients");
            continue;
          }
          //send the message back to client
          fwrite($sock, $data);

          foreach($this->clients as $client){
            if($client->getSocket() != $sock){
              fwrite($client->getSocket(), stream_socket_get_name($client->getSocket(), true) . ' says "' . $data . '"');
            }
          }
        }
      }
    }
  }
