<?php

class EasyForm {

  /**
   * form 
   * 
   * @var Form
   * @access private
   */
  private $form = null;

  /**
   * object 
   * 
   * @var PorkRecord
   * @access private
   */
  private $object = null;

  /**
   * __construct 
   * 
   * @param mixed $object 
   * @param array $inputs 
   * @access public
   * @return void
   */
  public function __construct($object, $inputs) {
    $this->original_object = $object;
    if(isset($inputs['path'])) {
      if(is_array($inputs['path'])) {
        $this->form = new Form($object, $inputs['path'][0], $inputs['path'][1]);
      } else {
        $this->form = new Form($object, $inputs['path']);
      }
    } else {
      $this->form = new Form($object);
    }
    $this->object = $this->form->get_object();
    $this->proceed_inputs($inputs);
    $this->form->end();
  }

  /**
   * proceed_inputs 
   * 
   * @param array $inputs 
   * @access private
   * @return void
   */
  private function proceed_inputs($inputs) {
    echo $this->do_fields($inputs['inputs']);
    echo $this->do_buttons(isset($inputs['buttons']) ? $inputs['buttons'] : array());
  }

  /**
   * do_fields 
   * 
   * @param array $inputs 
   * @access private
   * @return string
   */
  private function do_fields($inputs) {
    $output = "<fieldset class=\"inputs\"><ol>";
    foreach($inputs as $input_name => $input_options) {
      $type = $input_options[0];
      $label = $input_options[1];
      $output .= "<li class=\"".$type."\">";
      if($type != "boolean") {
        $output .= $this->form->label($input_name, $label);
      }
      switch($type) {
      case "string":
        $output .= $this->form->text_field($input_name);
        break;
      case "text":
        $output .= $this->form->textarea($input_name);
        break;
      case "boolean":
        $output .= "<label for=\"{$this->object->resource()}_{$input_name}\">";
        $output .= $this->form->checkbox($input_name);
        $output .= "$label</label>";
        break;
      case "ckeditor":
        $output .= $this->form->textarea($input_name, array('class' => 'ckeditor'));
        break;
      case "select":
        $output .= $this->form->select($input_name, $input_options[2], $input_options[3]);
        break;
      case "file":
        $output .= $this->form->file($input_name);
        break;
      case "date":
        $output .= $this->form->date_select($input_name, $input_options[2]);
        break;
      case "datetime":
        $output .= $this->form->datetime_select($input_name, $input_options[2]);
        break;
      }
      $errors = $this->object->get_errors();
      if(isset($errors[$input_name])) {
        $output .= "<p class=\"inline-errors\">";
        $output .= join(", ", $errors[$input_name]);
        $output .= "</p>";
      }
      $output .= "</li>";
    }
    $output .= "</ol></fieldset>";
    return $output;
  }


  /**
   * do_buttons 
   * 
   * @param array $buttons 
   * @access private
   * @return string
   */
  private function do_buttons($buttons = array()) {
    if(isset($buttons['submit'])) {
      $submit_text = $buttons['submit'];
    } else {
      $submit_text = $this->object->is_new_record() ? "Создать" : "Сохранить";
    }
    if(isset($buttons['cancel_path'])) {
      $cancel_path = $buttons['cancel_path'];
    } else {
      if(is_array($this->original_object)) {
        $cancel_path = $this->original_object[0]."_".$this->original_object[1]->resources();
      } else {
        $cancel_path = $this->original_object->resources();
      }
    }
    if(isset($buttons['cancel_url'])) {
      $cancel_url = $buttons['cancel_url'];
    } else {
      $cancel_url = Router::load()->path_to($cancel_path);
    }

    $output = "<fieldset class=\"buttons\"><ol>";

    $output .= "<li class=\"commit\">";
    $output .= $this->form->submit($submit_text);
    $output .= "</li>";

    $output .= "<li class=\"cancel\">";
    $output .= "<a href=".$cancel_url.">Отмена</a>";
    $output .= "</li>";

    $output .= "</ol></fieldset>";
    return $output;
  }
        

}

?>
