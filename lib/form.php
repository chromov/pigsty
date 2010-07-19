<?php

class Form {

  /**
   * action 
   * 
   * @var string
   * @access private
   */
  private $action = "";

  /**
   * method 
   * 
   * @var string
   * @access private
   */
  private $method = "";

  /**
   * object 
   * 
   * @var mixed
   * @access private
   */
  private $object = null;

  /**
   * __construct 
   * 
   * @param mixed $object 
   * @param string $route_name 
   * @param array $fixed_params 
   * @param string $method 
   * @access public
   * @return Form
   */
  public function __construct($object, $route_name = "", $fixed_params = array(), $method = "post") {
    $this->method = $method;
    $this->object = $object;
    if($route_name == "") { // guess route from the given $object
      if($this->object->is_new_record()) {
        $this->action = Router::load()->path_to($this->object->resources());
      } else {
        $this->action = Router::load()->path_to("update_".$this->object->resource(), array('id' => $this->object->ID));
      }
    } else {
      $this->action = Router::load()->path_to($route_name, $fixed_params);
    }
    echo "<form action='{$this->action}' method='{$this->method}'>";
  }

  /**
   * tag 
   * 
   * @param string $action 
   * @param string $method 
   * @static
   * @access public
   * @return string
   */
  static public function tag($action = "", $method = "post") {
    return "<form action='{$action}' method='{$method}'>";
  }

  /**
   * label_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @param string $text 
   * @static
   * @access public
   * @return string
   */
  static public function label_tag($object, $field, $text) {
    return "<label for='{$object->resource()}_{$field}'>{$text}</label>";
  }

  /**
   * text_field_tag 
   * 
   * @param mixed $object 
   * @param mixed $field 
   * @static
   * @access public
   * @return string
   */
  static public function text_field_tag($object, $field) {
    return "<input type='text' class='text' name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}' value='{$object->$field}' />";
  }

  /**
   * date_input_tag 
   * 
   * @param string $resource 
   * @param string $field_name 
   * @param string $date 
   * @param boolean $has_empty 
   * @param string $start_year 
   * @param string $end_year 
   * @static
   * @access public
   * @return string
   */
  static public function date_input_tag($resource, $field_name, $date = NULL, $has_empty = false, $start_year = "1950", $end_year = "2050") {
    if ($date == NULL) {
      $date = date("d-m-Y");
    } else {
      $has_empty = false;
    }
    $date_arr = getdate(strtotime($date));
    $output = '<select class="date_input" id="'.$resource.'_'.$field_name.'" name="'.$resource.'['.$field_name.'][day]">';
    if ($has_empty) $output .= '<option value="0">--</option>';
    for($d = 1; $d <= 31; $d++) {
      $output .= '<option ';
      if(!$has_empty && ($date_arr['mday'] == $d)) $output .= 'selected="selected" ';
      $output .= 'value="'.$d.'">'.sprintf("%02d",$d).'</option>';
    }
    $output .= '</select><select name="'.$resource.'['.$field_name.'][month]">';
    if ($has_empty) $output .= '<option value="0">--</option>';
    for($m = 1; $m <= 12; $m++) {
      $output .= '<option ';
      if(!$has_empty && ($date_arr['mon'] == $m)) $output .= 'selected="selected" ';
      $output .= 'value="'.$m.'">'.sprintf("%02d",$m).'</option>';
    }
    $output .= '</select><select name="'.$resource.'['.$field_name.'][year]">';
    if ($has_empty) $output .= '<option value="0">----</option>';
    for($y = (int)$start_year; $y <= (int)$end_year; $y++) {
      $output .= '<option ';
      if(!$has_empty && ($date_arr['year'] == $y)) $output .= 'selected="selected" ';
      $output .= 'value="'.$y.'">'.$y.'</option>';
    }
    $output .= '</select>';
    return $output;
  }

  /**
   * date_select_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @param boolean $has_empty 
   * @param string $start_year 
   * @param string $end_year 
   * @static
   * @access public
   * @return string
   */
  static public function date_select_tag($object, $field, $has_empty = false, $start_year = "1950", $end_year = "2050") {
    return self::date_input_tag($object->resource(), $field, $object->$field, $has_empty = false, $start_year, $end_year);
  }

  /**
   * select_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @param string $option_tags 
   * @static
   * @access public
   * @return string
   */
  static public function select_tag($object, $field, $option_tags) {
    $output = "<select name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}'>\n";
    $output .= $option_tags;
    $output .= "</select>\n";
    return $output;
  }

  /**
   * proceed_options 
   * 
   * @param array $options 
   * @static
   * @access private
   * @return string
   */
  static private function proceed_options($options = array()) {
    $output = "";
    if(isset($options['include_blank']) && $options['include_blank']) {
      $output .= "<option value=''>";
      if(is_string($options['include_blank'])) {
        $output .= $options['include_blank'];
      }
      $output .= "</option>\n";
    }
    return $output;
  }

  /**
   * select 
   * 
   * @param string $field 
   * @param array $options_array 
   * @param array $options 
   * @access public
   * @return string
   */
  public function select($field, $options_array = array(), $options = array()) {
    $output = self::proceed_options($options);
    foreach($options_array as $val => $key) {
      $output .= "<option value='{$key}'".($this->object->$field == $object->$value_property ? " selected='selected'" : "").">{$val}</option>\n";
    }
    return self::select_tag($this->object, $field, $output);
  }

  /**
   * collection_select 
   * 
   * @param string $field 
   * @param object $collection 
   * @param string $value_method 
   * @param string $text_method 
   * @param array $options 
   * @access public
   * @return string
   */
  public function collection_select($field, $collection, $value_property, $text_property, $options = array()) {
    $output = self::proceed_options($options);
    foreach($collection as $object) {
      $output .= "<option value='{$object->$value_property}'".($this->object->$field == $object->$value_property ? " selected='selected'" : "").">{$object->$text_property}</option>\n";
    }
    return self::select_tag($this->object, $field, $output);
  }

  /**
   * label 
   * 
   * @param string $field 
   * @param string $text 
   * @access public
   * @return string
   */
  public function label($field, $text) {
    return self::label_tag($this->object, $field, $text);
  }

  /**
   * date_select 
   * 
   * @param string $field 
   * @param boolean $has_empty 
   * @param string $start_year 
   * @param string $end_year 
   * @access public
   * @return string
   */
  public function date_select($field, $has_empty = false, $start_year = "1950", $end_year = "2050") {
    return self::date_select_tag($this->object, $field, $has_empty = false, $start_year, $end_year);
  }

  /**
   * text_field 
   * 
   * @param string $field 
   * @access public
   * @return string
   */
  public function text_field($field) {
    return self::text_field_tag($this->object, $field);
  }

  /**
   * submit 
   * 
   * @param string $value 
   * @param string $name 
   * @static
   * @access public
   * @return string
   */
  static public function submit($value = "send", $name = "submit") {
    return "<input type='submit' value='{$value}' name='{$name}' />";
  }

  /**
   * close 
   * 
   * @static
   * @access public
   * @return string
   */
  static public function close() {
    return "</form>";
  }

}

?>