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
   * attrs 
   * 
   * @var string
   * @access private
   */
  private $attrs = "";

  /**
   * pre_checkboxes 
   * 
   * @var array
   * @access private
   */
  private $pre_checkboxes = array();

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
  public function __construct($object, $route_name = "", $fixed_params = array(), $method = "post", $options = array()) {
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
    foreach ($options as $key => $option) {
      $this->attrs .= " {$key} = '{$option}'";
    }
    ob_start();
  }


  /**************************************
   **  CLASS METHODS
   **************************************/


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
   * hidden_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @param string $value 
   * @static
   * @access public
   * @return string
   */
  static public function hidden_tag($object, $field, $value = NULL) {
    if($value != NULL) {
      $input_val = $value;
    } else {
      $input_val = $object->$field;
    }
    return "<input type='hidden' name='{$object->resource()}[{$field}]' value='{$input_val}' />";
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
   * @param PorkRecord $object 
   * @param string $field 
   * @param boolean $safe 
   * @static
   * @access public
   * @return string
   */
  static public function text_field_tag($object, $field, $safe = true) {
    if($safe) {
      $input_val = htmlspecialchars($object->$field, ENT_QUOTES);
    } else {
      $input_val = $object->$field;
    }
    return "<input type='text' class='text' name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}' value='{$input_val}' />";
  }

  /**
   * textarea_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @param array $options 
   * @param boolean $safe 
   * @static
   * @access public
   * @return string
   */
  static public function textarea_tag($object, $field, $options = array(), $safe = false) {
    if($safe) {
      $input_val = htmlspecialchars($object->$field, ENT_QUOTES);
    } else {
      $input_val = $object->$field;
    }
    $attrs = "";
    foreach ($options as $key => $opt) {
      $attrs .= " {$key} = '{$opt}'";
    }
    return "<textarea name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}'{$attrs}>\n{$input_val}\n</textarea>";
  }

  /**
   * file_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @static
   * @access public
   * @return string
   */
  static public function file_tag($object, $field) {
    return "<input type='file' name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}' class='file_upload' />";
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
    }
    $date_arr = date_parse($date);
    $output = '<select class="date_input" id="'.$resource.'_'.$field_name.'" name="'.$resource.'['.$field_name.'][day]">';
    if ($has_empty) $output .= '<option value="0">--</option>';
    for($d = 1; $d <= 31; $d++) {
      $output .= '<option ';
      if($date_arr['day'] == $d) $output .= 'selected="selected" ';
      $output .= 'value="'.$d.'">'.sprintf("%02d",$d).'</option>';
    }
    $output .= '</select><select name="'.$resource.'['.$field_name.'][month]">';
    if ($has_empty) $output .= '<option value="0">--</option>';
    for($m = 1; $m <= 12; $m++) {
      $output .= '<option ';
      if($date_arr['month'] == $m) $output .= 'selected="selected" ';
      $output .= 'value="'.$m.'">'.sprintf("%02d",$m).'</option>';
    }
    $output .= '</select><select name="'.$resource.'['.$field_name.'][year]">';
    if ($has_empty) $output .= '<option value="0">----</option>';
    for($y = (int)$start_year; $y <= (int)$end_year; $y++) {
      $output .= '<option ';
      if($date_arr['year'] == $y) $output .= 'selected="selected" ';
      $output .= 'value="'.$y.'">'.$y.'</option>';
    }
    $output .= '</select>';
    return $output;
  }

  /**
   * date_input_tag 
   * 
   * @param string $resource 
   * @param string $field_name 
   * @param string $time 
   * @param boolean $has_empty 
   * @static
   * @access public
   * @return string
   */
  static public function time_input_tag($resource, $field_name, $date = NULL, $has_empty = false) {
    if ($date == NULL) {
      $date = date("d-m-Y H:i");
    }
    $date_arr = getdate(strtotime($date));
    $output = '<select id="'.$resource.'_'.$field_name.'" name="'.$resource.'['.$field_name.'][hours]">';
    if ($has_empty) $output .= '<option value="0">--</option>';
    for($h = 0; $h <= 23; $h++) {
      $output .= '<option ';
      if($date_arr['hours'] == $h) $output .= 'selected="selected" ';
      $output .= 'value="'.$h.'">'.sprintf("%02d",$h).'</option>';
    }
    $output .= '</select><select name="'.$resource.'['.$field_name.'][min]">';
    if ($has_empty) $output .= '<option value="0">----</option>';
    for($m = 0; $m <= 59; $m++) {
      $output .= '<option ';
      if($date_arr['minutes'] == $m) $output .= 'selected="selected" ';
      $output .= 'value="'.$m.'">'.sprintf("%02d",$m).'</option>';
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
    return self::date_input_tag($object->resource(), $field, $object->$field, $has_empty, $start_year, $end_year);
  }

  /**
   * datetime_select_tag 
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
  static public function datetime_select_tag($object, $field, $has_empty = false, $start_year = "1950", $end_year = "2050") {
    return self::time_input_tag($object->resource(), $field, $object->$field, $has_empty)."<span>&nbsp;</span>".self::date_input_tag($object->resource(), $field, $object->$field, $has_empty, $start_year, $end_year);
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
  static public function select_tag($object, $field, $option_tags, $select_many = false) {
    $output = "<select name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}'".($select_many ? " class='select_many'" : '').">\n";
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
   * checkbox_tag 
   * 
   * @param PorkRecord $object 
   * @param string $field 
   * @static
   * @access private
   * @return string
   */
  static private function checkbox_tag($object, $field) {
    if($object->$field) $checked = " checked='yes'";
    return "<input type='checkbox' name='{$object->resource()}[{$field}]' id='{$object->resource()}_{$field}' value='1'{$checked} />";
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
  static public function submit_tag($value = "send", $name = "submit") {
    return "<input type='submit' value='{$value}' name='{$name}' />";
  }

  /**************************************
   **  INSTANCE METHODS
   **************************************/


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
   * select_many 
   * 
   * @param string $relation 
   * @param string $text_property 
   * @param string $empty_option 
   * @access public
   * @return string
   */
  public function select_many($relation, $text_property = "name", $empty_option = "-----") {
    $class_name = Utils::classify($relation);
    if(!class_exists($class_name)) return "Wrong relation name!";

    $collection = $class_name::find();
    if(!$this->object->is_new_record()) {
      $current = $this->object->$relation();
      $collection = array_diff($collection, $current);
    }

    $output = "<option value=''>".$empty_option."</option>";
    foreach($collection as $object) {
      $output .= "<option value='{$object->ID}'>{$object->$text_property}</option>\n";
    }

    $current_options = "";
    foreach($current as $c_opt) {
      $current_options .= "<div class='select_many_check' id='{$this->object->resource()}_{$relation}_{$c_opt->ID}'><input type='checkbox' value='{$c_opt->ID}' checked='checked' name='{$this->object->resource()}[{$relation}][]' class='{$this->object->resource()}_{$relation}' /><span>{$c_opt->$text_property}</span></div>\n";
    }
    $select = self::select_tag($this->object, $relation, $output, true);

    return $select.$current_options;
  }

  /**
   * hidden 
   * 
   * @param string $field 
   * @param string $value 
   * @access public
   * @return string
   */
  public function hidden($field, $value) {
    return self::hidden_tag($this->object, $field, $value);
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
    return self::date_select_tag($this->object, $field, $has_empty, $start_year, $end_year);
  }

  /**
   * datetime_select 
   * 
   * @param string $field 
   * @param boolean $has_empty 
   * @param string $start_year 
   * @param string $end_year 
   * @access public
   * @return string
   */
  public function datetime_select($field, $has_empty = false, $start_year = "1950", $end_year = "2050") {
    return self::datetime_select_tag($this->object, $field, $has_empty, $start_year, $end_year);
  }

  /**
   * text_field 
   * 
   * @param string $field 
   * @param boolean $safe 
   * @access public
   * @return string
   */
  public function text_field($field, $safe = true) {
    return self::text_field_tag($this->object, $field, $safe);
  }

  /**
   * textarea 
   * 
   * @param string $field 
   * @param array $options 
   * @param boolean $safe 
   * @access public
   * @return string
   */
  public function textarea($field, $options = array(), $safe = false) {
    return self::textarea_tag($this->object, $field, $options, $safe);
  }

  /**
   * file 
   * 
   * @param string $field 
   * @access public
   * @return string
   */
  public function file($field) {
    $this->attrs .= " enctype='multipart/form-data'";
    return self::file_tag($this->object, $field);
  }

  /**
   * checkbox 
   * 
   * @param string $field 
   * @access public
   * @return string
   */
  public function checkbox($field) {
    $this->pre_checkboxes[] = $field;
    return self::checkbox_tag($this->object, $field);
  }

  /**
   * submit 
   * 
   * @param string $value 
   * @param string $name 
   * @access public
   * @return string
   */
  public function submit($value = "send", $name = "submit") {
    return self::submit_tag($value, $name);
  }

  /**
   * end 
   * 
   * @access public
   * @return void
   */
  public function end() {
    $result = ob_get_contents();
    ob_end_clean();
    echo "<form action='{$this->action}' method='{$this->method}'{$this->attrs}>\n";
    foreach($this->pre_checkboxes as $pc) {
      echo $this->hidden($pc, '0');
    }
    echo $result;
    echo "\n</form>\n";
  }

}

?>
