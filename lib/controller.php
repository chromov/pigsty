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
   * render_options 
   * The place to strore rendering parameters. Changing through render() method
   * 
   * @var array
   * @access private
   */
  private $render_options = array();

  /**
   * __construct 
   * 
   * @param array $params 
   * @access protected
   * @return void
   */
  public function __construct($params) {
    $this->params = $params;

    $this->render_options['module_layout'] = '';
    $this->render_options['facet_layout'] = '';
  }

  /**
   * __destruct 
   * 
   * @access public
   * @return void
   */
  public function __destruct() {
    $base = $_SERVER['DOCUMENT_ROOT']."/";
    $base = str_replace('//', '/', $base);
    $base .= "facets/".$this->params['facet']."/modules/".$this->params['module']."/views/".$this->params['controller']."/";

    // prepearing an output
    $output = $this->prepare_template($base.$this->params['action'].".html.php");

    $module_layout = "html.php";
    if ($this->render_options['module_layout']) {
      $module_layout = $this->render_options['module_layout'].".".$module_layout;
    }
    $output = $this->prepare_template($base."../../layouts/".$module_layout, $output);

    $facet_layout = "html.php";
    if ($this->render_options['facet_layout']) {
      $facet_layout = $this->render_options['facet_layout'].".".$facet_layout;
    }
    $output = $this->prepare_template($base."../../../../layouts/".$facet_layout, $output);

    //sending it out
    echo $output;
  }

  /**
   * render 
   * Gives the ability to change what will be rendered
   * 
   * @param array $params 
   * @access private
   * @return boolean
   */
  private function render($params = array()) {
    foreach ($params as $key => $value) {
      $this->render_options[$key] = $value;
    }
    return true;
  }

  /**
   * prepare_template 
   * 
   * @param string $file_path 
   * @param string $inner_content 
   * @access private
   * @return string
   */
  private function prepare_template($file_path, $inner_content = "") {
    if (!file_exists($file_path)) {
      return "<strong>Can't find template <em>$file_path</em></strong><br/>\n".$inner_content;
    }
    ob_start();
    require($file_path);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
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
    "<a href=".Router::load()->path_to($route_name, $fixed_params, $query_params).">".$link_text."</a>";
  }

}

?>
