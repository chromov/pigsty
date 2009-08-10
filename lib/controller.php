<?php

/**
 * Controller class
 * This class is responsible for all site controllers
 */
class Controller {

  /**
   * params 
   * 
   * @var array
   * @access private
   */
  private $params = array();

  /**
   * __construct 
   * 
   * @param array $params 
   * @access protected
   * @return void
   */
  function __construct($params) {
    $this->params = $params;
  }

  /* Helper methods */

  /**
   * link_to 
   * Generates link tag
   * 
   * @param string $link_text Text of the link
   * @param string $route_name The name of the route to use
   * @param array $fixed_params List of params to insert into route template
   * @param array $query_params Additional parameters
   * @access public
   * @return void
   */
  public function link_to($link_text, $route_name, $fixed_params=array(), $query_params=array()) {
    "<a href=".Router::path_to($route_name, $fixed_params, $query_params).">".$link_text."</a>";
  }

}

?>
