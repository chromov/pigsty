<?php

require_once('lib/bbcode/bbcode.php');

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
  protected $params = array();

  /**
   * render_options 
   * The place to strore rendering parameters. Changing through render() method
   * 
   * @var array
   * @access private
   */
  protected $render_options = array();

  /**
   * headers_sent 
   * 
   * @var boolean
   * @access public
   */
  public $headers_sent = false;

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

    $base = $_SERVER['DOCUMENT_ROOT']."/";
    $base = str_replace('//', '/', $base);
    $this->doc_root = $base;

    $this->before_filter();
  }

  /**
   * __destruct 
   * 
   * @access public
   * @return void
   */
  public function __destruct() {
    $this->after_filter();

    if ($this->headers_sent) {
      return;
    }
    $base = $this->doc_root;
    $base .= "facets/".$this->params['facet']."/modules/".$this->params['module']."/views/".$this->params['controller']."/";

    // prepearing an output
    $output = $this->prepare_template($base.$this->params['action'].".html.php");

    $module_layout = "html.php";
    if ($this->render_options['module_layout'] !== '') {
      $module_layout = $this->render_options['module_layout'];
    }
    if ($module_layout) {
      $output = $this->prepare_template($base."../../layouts/".$module_layout, $output);
    }

    $facet_layout = "html.php";
    if($this->render_options['facet_layout'] !== '') {
      $facet_layout = $this->render_options['facet_layout'];
    }
    if($facet_layout) {
      $output = $this->prepare_template($base."../../../../layouts/".$facet_layout, $output);
    }

    //sending it out
    echo $output;
  }

  /**
   * before_filter 
   * 
   * @access protected
   * @return void
   */
  protected function before_filter() {

  }

  /**
   * after_filter 
   * 
   * @access protected
   * @return void
   */
  protected function after_filter() {
    
  }

  /**
   * render 
   * Gives the ability to change what will be rendered
   * 
   * @param array $params 
   * @access private
   * @return boolean
   */
  protected function render($params = array()) {
    foreach ($params as $key => $value) {
      $this->render_options[$key] = $value;
    }
    return true;
  }

  /**
   * render_partial 
   * 
   * @param string $partial 
   * @param array $vars 
   * @param array $params 
   * @access protected
   * @return string
   */
  protected function render_partial($partial, $vars=array(), $params=array()) {
    $p_params = $this->params;
    if($params) {
      foreach($params as $key => $val) {
        $p_params[$key] = $val;
      }
    }
    $partial_path = $this->doc_root."facets/{$p_params['facet']}/modules/{$p_params['module']}/views/{$p_params['controller']}/_{$partial}.html.php";
    return $this->prepare_template($partial_path, "", $vars);
  }

  /**
   * prepare_template 
   * 
   * @param string $file_path 
   * @param string $inner_content 
   * @access private
   * @return string
   */
  private function prepare_template($file_path, $inner_content = "", $vars=array()) {
    if (!file_exists($file_path)) {
      return "<strong>Can't find template <em>$file_path</em></strong><br/>\n".$inner_content;
    }
    if($vars) {
      foreach($vars as $key => $val) {
        $$key = $val;
      }
    }
    ob_start();
    require($file_path);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
  }

  /**
   * redirect_to 
   * 
   * @param string $route_name 
   * @param array $fixed_params 
   * @param array $query_params 
   * @access public
   * @return void
   */
  protected function redirect_to($route_name, $fixed_params=array(), $query_params=array()) {
    $path = Router::load()->path_to($route_name, $fixed_params, $query_params);
    header("location: {$path}");
    $this->headers_sent = true;
  }

  /**
   * safe_params 
   * Strips html tags from the fields of passed array. And leaves untouched
   * fields listed in $safe_keys parameter
   * 
   * @param array $params 
   * @param array $safe_keys 
   * @access public
   * @return array
   */
  public function safe_params($params, $safe_keys=array()) {
    $safe = array();
    if(is_array($params)) {
      foreach($params as $key => $val) {
        if(!in_array($key, $safe_keys)) {
          $safe[$key] = strip_tags($val);
        } else {
          $safe[$key] = $val;
        }
      }
    } else {
      return false;
    }
    return $safe;
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
   * @return string
   */
  protected function link_to($link_text, $route_name, $fixed_params=array(), $query_params=array()) {
    return "<a href=".Router::load()->path_to($route_name, $fixed_params, $query_params).">".$link_text."</a>";
  }

  /**
   * bb2html 
   * 
   * @param string $text 
   * @access protected
   * @return string
   */
  protected function bb2html($text) {
    $bb = new bbcode($text);
    return $bb->get_html();
  }

}

?>
