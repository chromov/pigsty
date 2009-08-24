<?php

  array_walk(glob('./lib/*.php'),create_function('$v,$i', 'return require_once($v);')); 
  require_once('./config/routes.php');

  $__params = Router::load()->parse_URI($_GET['URI__'], strtolower($_SERVER['REQUEST_METHOD']));
  foreach ($_POST as $key => $val) {
    if (!array_key_exists($key, $__params)) $__params[$key] = $val;
  }
  foreach ($_GET as $key => $val) {
    if (($key != "URI__") && !array_key_exists($key, $__params)) $__params[$key] = $val;
  }

  require_once("./facets/".$__params['facet']."/modules/".$__params['module']."/controllers/".$__params['controller']."_controller.php");
  $controller_name = ucwords($__params['controller'])."Controller";
  $controller = new $controller_name($__params);
  call_user_func(array($controller, $__params['action']));

?>
