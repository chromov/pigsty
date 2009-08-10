<?php

/*
 * Router
 * Provides routing functionality
 */
class Router {

  /**
   * routes 
   * configuration array
   * 
   * @static
   * @var array
   * @access private
   */
  private static $routes = array();

  /**
   * result_routes 
   * 
   * @var array
   * @access private
   */
  private $result_routes = array();

  /**
   * localized 
   * 
   * @var boolean
   * @access private
   */
  private $localized = true;

  /**
   * result_parameters 
   * 
   * @var array
   * @access private
   */
  private $result_parameters = array();

  /**
   * instance 
   * 
   * @static
   * @var Router
   * @access private
   */
  private static $instance = NULL;

  /**
   * load 
   * 
   * @static
   * @access public
   * @return Router
   */
  public static function load() {
    if (self::$instance == NULL)
    {
      self::$instance = new Router();
    }
    return self::$instance;
  }

  /**
   * __construct 
   * 
   * @access private
   * @return void
   */
  private function __construct() {
    //parse routes
    foreach (self::$routes as $facet_name => $facet_body) {
      $this->add_result_routes($this->parse_facet($facet_body, $facet_name));
    }
  }

  private function __clone() {
  }

  /**
   * parse_facet 
   * 
   * @param array $facet_body 
   * @param string $facet_name 
   * @access private
   * @return array
   */
  private function parse_facet($facet_body, $facet_name) {
    $got_routes = array();
    foreach ($facet_body['modules'] as $module_name => $module_body) {
      $got_routes += $this->parse_module($module_body, $module_name);
    }
    foreach ($got_routes as $route_name => $route_body) {
      if(!$facet_body['default']) { $route_body['route'] = $facet_name."/".$route_body['route']; }
      $route_body['facet'] = $facet_name;
      $got_routes[$route_name] = $route_body;
    }
    return $got_routes;
  }

  /**
   * parse_module 
   * 
   * @param array $module_body 
   * @param string $module_name 
   * @access private
   * @return array
   */
  private function parse_module($module_body, $module_name) {
    $got_routes = array();
    foreach ($module_body as $record_name => $record_body) {
      if ($record_body['type'] == "resource") {
        $got_routes += $this->parse_resource($record_body, $record_name);
      } else {
        $got_routes += array($record_name => $record_body);
      }
    }
    foreach ($got_routes as $route_name => $route_body) {
      $route_body['route'] = $module_name."/".$route_body['route'];
      $route_body['module'] = $module_name;
      $got_routes[$route_name] = $route_body;
    }
    return $got_routes;
  }

  /**
   * parse_resource 
   * 
   * @param array $res_route 
   * @access private
   * @return array
   */
  private function parse_resource($res_route, $res_name, $parent = NULL) {
    $got_routes = array();
    $actions = array("index" => "get", "show" => "get", "new" => "get", "create" => "post", "edit" => "get", "update" => "post", "destroy" => "get");

    if ($parent == NULL) {
      foreach ($actions as $action => $method) {
        $route_val = "";
        switch($action) {
        case "edit":
        case "destroy":
          $route_val = "/".$action;
        case "show":
        case "update":
          $route_val = "{id:int}".$route_val;
          break;
        case "new":
          $route_val = $action;
          break;
        }

        $res_action_name = $action == "index" ? $res_name : $action."_".Inflect::singularize($res_name);
        if(!$res_route['default']) {
          $route_val = $route_val == "" ? $res_name : $res_name."/".$route_val;
        }
        $new_route[$res_action_name] = array(
          "route" => $route_val,
          "controller" => $res_name,
          "action" => $action,
          "method" => $method
        );
        $got_routes += $new_route;
      }
    }

    if(is_array($res_route['nested'])) {
      foreach ($res_route['nested'] as $nested_name => $nested_route) {
        $got_routes = $got_routes + $this->parse_resource($nested_route, $nested_name, $res_name);
      }
    }
    return $got_routes;
  }


  /**
   * add_route 
   * 
   * @param array $new_routes The array of routes to add, in the same format as Routing::routes
   * @static
   * @access public
   * @return boolean
   */
  public static function add_routes($new_routes) {
    if (count(array_intersect_key(self::$routes, $new_routes)) > 0) {
      return false;
    }
    self::$routes = array_merge(self::$routes, $new_routes);
    return true;
  }

  /**
   * add_result_routes 
   * 
   * @param array $new_routes 
   * @access private
   * @return boolean
   */
  private function add_result_routes($new_routes) {
    if (count(array_intersect_key($this->result_routes, $new_routes)) > 0) {
      return false;
    }
    $this->result_routes = array_merge($this->result_routes, $new_routes);
    return true;
  }

  public function get_result_routes() {
    return $this->result_routes;
  }

  public static function get_routes() {
    return self::$routes;
  }

  /**
   * set_localized 
   * 
   * @param boolean $val 
   * @access public
   * @return void
   */
  public function set_localized($val) {
    $this->localized = $val;
  }

  /**
   * parse_URI 
   * Parses $URI parameter and stores the result in $result_parameters
   * 
   * @param String $URI String to be parsed
   * @access public
   * @return array
   */
  public function parse_URI($URI) {
    if ($URI == "") {
      return false;
    }
    $this->result_parameters = array();
    return $this->result_parameters;
  }

  /**
   * get_params 
   * 
   * @access public
   * @return array
   */
  public function get_params() {
    return $this->result_parameters;
  }

  /**
   * path_to 
   * Generates url string
   * 
   * @param string $route_name 
   * @param array $fixed_params 
   * @param array $query_params 
   * @access public
   * @return string
   */
  public function path_to($route_name, $fixed_params=array(), $query_params=array()) {
    return "/generated/path/";
  }
}

?>
