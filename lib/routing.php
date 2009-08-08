<?php

/*
 * Routing
 * Provides routing functionality
 */

class Routing {

  /**
   * routes 
   * configuration array
   * 
   * @static
   * @var array
   * @access private
   */
  private static $routes = array(
    "articles" => "/{lang:str}/articles.{format:str}"
    "show_article" => "/{lang:str}/articles/{id:int}/{title:str}.{format:str}"
  );

  /**
   * result_parameters 
   * 
   * @static
   * @var array
   * @access private
   */
  private static $result_parameters = array();

  /**
   * parse_URI 
   * Parses $URI parameter and stores the result in $result_parameters
   * 
   * @param String $URI String to be parsed
   * @static
   * @access public
   * @return array
   */
  public static function parse_URI($URI) {
    result_parameters = array();
    return $this->result_parameters;
  }

  /**
   * get_params 
   * 
   * @static
   * @access public
   * @return array
   */
  public static function get_params() {
    return $this->result_parameters;
  }

  /**
   * path_to 
   * Generates url string
   * 
   * @param string $route_name 
   * @param array $fixed_params 
   * @param array $query_params 
   * @static
   * @access public
   * @return string
   */
  public static function path_to($route_name, $fixed_params=array(), $query_params=array()) {
    return "/generated/path/";
  }
}

?>
