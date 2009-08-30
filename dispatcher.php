<?php

if(!function_exists('get_called_class')) {
  function get_called_class() {
    $bt = debug_backtrace();
    $l = 0;
    do {
        $l++;
        $lines = file($bt[$l]['file']);
        $callerLine = $lines[$bt[$l]['line']-1];
        preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                   $callerLine,
                   $matches);
                  
       if ($matches[1] == 'self') {
               $line = $bt[$l]['line']-1;
               while ($line > 0 && strpos($lines[$line], 'class') === false) {
                   $line--;                  
               }
               preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
       }
    }
    while ($matches[1] == 'parent'  && $matches[1]);
    return $matches[1];
  }
}

  array_walk(glob('./lib/*.php'),create_function('$v,$i', 'return require_once($v);')); 
  array_walk(glob('./config/*.php'),create_function('$v,$i', 'return require_once($v);')); 
  array_walk(glob('./models/*.php'),create_function('$v,$i', 'return require_once($v);')); 

  $__params = Router::load()->parse_URI($_GET['URI__'], strtolower($_SERVER['REQUEST_METHOD']));
  if ($__params === false) {
    throw new Exception("No route");
  }

  foreach ($_POST as $key => $val) {
    if (!array_key_exists($key, $__params)) $__params[$key] = $val;
  }
  foreach ($_GET as $key => $val) {
    if (($key != "URI__") && !array_key_exists($key, $__params)) $__params[$key] = $val;
  }

  require_once("./facets/".$__params['facet']."/modules/".$__params['module']."/controllers/".$__params['controller']."_controller.php");
  $controller_name = ucwords($__params['controller'])."Controller";
  $controller = new $controller_name($__params);
  call_user_func(array($controller, $__params['action']."_action"));

?>
