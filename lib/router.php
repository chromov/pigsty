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
   * with_unlocalized 
   * 
   * @var boolean
   * @access public
   */
  public static $with_unlocalized = false;

  /**
   * default_facet 
   * 
   * @var string
   * @access private
   */
  private $default_facet = "";

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
   * set_with_unlocalized 
   * 
   * @param boolean $val 
   * @static
   * @access public
   * @return void
   */
  public static function set_with_unlocalized($val) {
    self::$with_unlocalized = $val;
  }

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
    if (array_key_exists('root', $facet_body)) {
      $route_body = $facet_body['root'];
      $route_body['facet'] = $facet_name;
      $root = array($facet_name."_root" => $route_body);
      $got_routes += $root;
    }
    $ul_routes = array();
    foreach ($got_routes as $route_name => $route_body) {
      if(!isset($facet_body['default']) || (isset($facet_body['default']) && $facet_body['default'] !== true)) {
        $route_body['route'] = $route_body['route'] == "" ? $facet_name : $facet_name."/".$route_body['route'];
      } else {
        $this->default_facet = $facet_name;
      }
      $route_body['facet'] = $facet_name;
      $route_body['route_name'] = $route_name;

      if(I18n::get_active()) {
        if(self::$with_unlocalized) {
          $ul_routes[$route_name."_unlocalized"] = $route_body;
          $ul_routes[$route_name."_unlocalized"]['route_name'] = $route_name."_unlocalized";
        }
        $route_body['route'] = "{locale:str}/".$route_body['route'];
      }

      $got_routes[$route_name] = $route_body;
    }
    return(array_merge($got_routes, $ul_routes));
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
      if (isset($record_body['type']) && $record_body['type'] == "resource") {
        $got_routes += $this->parse_resource($record_body, $record_name);
      } else {
        $got_routes += array($record_name => $record_body);
      }
    }
    foreach ($got_routes as $route_name => $route_body) {
      $route_body['route'] = $route_body['route'] == "" ? $module_name : $module_name."/".$route_body['route'];
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
    $parent_actions = array('index' => 'get', 'new' => 'get', 'create' => 'post');

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
        if(!isset($res_route['default']) || (isset($res_route['default']) && $res_route['default'] !== true)) {
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
    } else {
      foreach ($parent_actions as $action => $method) {
        $single_parent = Inflect::singularize($parent);
        $route_val = "{{$single_parent}_id:int}/".$res_name;
        if($action == 'new') {
          $route_val .= "/".$action;
        }

        $res_action_name = $action == "index" ? $res_name : $action."_".Inflect::singularize($res_name);
        $res_action_name = $single_parent."_".$res_action_name;

        $new_route[$res_action_name] = array(
          "route" => $route_val,
          "controller" => $res_name,
          "action" => $action."_for_".$single_parent,
          "method" => $method
        );
        $got_routes += $new_route;
      }
    }

    if(isset($res_route['nested']) && is_array($res_route['nested'])) {
      foreach ($res_route['nested'] as $nested_name => $nested_route) {
        $got_nested_routes = $this->parse_resource($nested_route, $nested_name, $res_name);
        foreach ($got_nested_routes as $key_route => $val_route) {
          if(!isset($res_route['default']) || (isset($res_route['default']) && $res_route['default'] !== true)) {
            $val_route['route'] = $res_name."/".$val_route['route'];
          }
          $got_routes[$key_route] = $val_route;
        }
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
   * get_default_facet 
   * 
   * @access public
   * @return mixed
   */
  public function get_default_facet() {
    if($this->default_facet) {
      return $this->default_facet;
    }
    return NULL;
  }

  /**
   * parse_URI 
   * Parses $URI parameter and stores the result in $result_parameters
   * 
   * @param String $URI String to be parsed
   * @param string $method HTTP request method
   * @access public
   * @return array
   */
  public function parse_URI($URI, $method = "get") {
    $matches = array();

    foreach($this->result_routes as $route_array) {
      $route_regexp = $this->regexpify($route_array['route']);
      //print($route_regexp."\n");
      if (preg_match($route_regexp, $URI, $matches) && $route_array['method'] == $method) {
        $this->result_parameters = array_merge($this->extract_hash($matches), $route_array);
        return $this->result_parameters;
      }
    }
    return false;
  }

  /**
   * extract_hash 
   * 
   * @param array $input_array 
   * @access private
   * @return array
   */
  private function extract_hash($input_array) {
    $res_array = array();
    foreach ($input_array as $key => $val) {
      if (!preg_match('/^\d+$/', $key)) {
        $res_array[$key] = $val;
      }
    }
    return $res_array;
  }

  /**
   * regexpify 
   * 
   * @param string $route 
   * @access private
   * @return string
   */
  private function regexpify($route) {
    if (preg_match('/{\w+:\w+}/', $route)) {
      $res = preg_replace_callback('/{(?<name>\w+):(?<type>\w+)}/', array($this, 'replace_variables'), $route);
    } else {
      $res = $route;
    }
    return "/^".addcslashes($res, "/")."$/";
  }

  /**
   * replace_variables 
   * 
   * @param array $matches 
   * @access private
   * @return string
   */
  private function replace_variables($matches) {
    switch ($matches['type']) {
      case "int":
        $regxp = "\d+";
        break;
      case "str":
        $regxp = "(\w|-)+";
        break;
    }
    return "(?<".$matches['name'].">".$regxp.")";
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
    if(isset($this->result_routes[$route_name])) {
      if(!isset($fixed_params['locale'])) {
        if(isset($this->result_parameters['locale'])) {
          $fixed_params['locale'] = $this->result_parameters['locale'];
        } else {
          $fixed_params['locale'] = I18n::$default_locale;
        }
      }
      $route = $this->result_routes[$route_name]['route'];
      foreach ($fixed_params as $key => $value) {
        if(is_string($value) || is_int($value)) {
          $route = preg_replace("/\{$key:\w+\}/", $value, $route);
        }
      }
      if (count($query_params) > 0) {
        $pairs = array();
        foreach ($query_params as $key => $value) {
          $pairs[] = "$key=$value";
        }
        $route = $route."?".implode("&", $pairs);
      }
      return "/".$route;
    } else {
      return "";
    }
  }

  public function url_to($route_name, $fixed_params=array(), $query_params=array()) { 
    return "http://{$_SERVER['SERVER_NAME']}".$this->path_to($route_name, $fixed_params, $query_params);
  }

}

?>
