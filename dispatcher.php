<?php

  require_once('lib/pigsty.php');

  try {
    $__params = Router::load()->parse_URI($_GET['URI__'], strtolower($_SERVER['REQUEST_METHOD']));
    if ($__params === false) {
      throw new Exception("No route");
    }
    if(I18n::get_active() && isset($__params['locale'])) {
      I18n::set_locale($__params['locale']);
    }

    foreach ($_POST as $key => $val) {
      if (!array_key_exists($key, $__params)) $__params[$key] = $val;
    }
    foreach ($_GET as $key => $val) {
      if (($key != "URI__") && !array_key_exists($key, $__params)) $__params[$key] = $val;
    }
    $files = array();
    foreach ($_FILES as $key => $file_array) {
      if(is_array($file_array['name'])) {
        $file_fields = array_keys($file_array);
        foreach(array_keys($file_array['name']) as $key_name) {
          $values = array();
          foreach($file_fields as $ff) {
            $values[$ff] = $file_array[$ff][$key_name];
          }
          $files[$key][$key_name] = new File($values);
        }
      } else {
        $files[$key] = new File($file_array);
      }
    }
    $__params = array_merge_recursive($files, $__params);

    require_once("./controllers/root_controller.php");
    require_once("./controllers/facets/".$__params['facet']."_controller.php");
    require_once("./facets/".$__params['facet']."/modules/".$__params['module']."/controllers/".$__params['controller']."_controller.php");
    $controller_name = Utils::first_caps($__params['controller'])."Controller";
    $controller = new $controller_name($__params);
    if(!$controller->headers_sent) {
      call_user_func(array($controller, $__params['action']."_action"));
    }
  } catch(Exception $e) {
    if(($_GET['URI__'] == "") && ($def_facet = Router::load()->get_default_facet())) {
      header("Location: ".Router::load()->path_to($def_facet."_root"));
    } else {
      header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
      include('public/404.html.php');
    }
  }

?>
