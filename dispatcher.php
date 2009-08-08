<?php

  array_walk(glob('./lib/*.php'),create_function('$v,$i', 'return require_once($v);')); 

  echo "we got request ".$_GET['URI__'];

?>
