<?php
  error_reporting(E_ALL);
  include('gTelnet.class.php');
  include('gTelnetClient.class.php');


  $server = new gTelnet();
  $server->run();

