<?php

  array_walk(glob('./lib/*.php'),create_function('$v,$i', 'return require_once($v);')); 
  require_once('./config/routes.php');

  echo "we got request ".$_GET['URI__'];

  Router::load()->parse_URI($_GET['URI__']);

?>
