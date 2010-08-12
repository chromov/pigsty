<?php

class File {

  /**
   * file_array 
   * 
   * @var array
   * @access private
   */
  private $file_array = array();

  public function __construct($file_array) {
    $this->file_array = $file_array;
  }

  public function __get($var) {
    if(in_array($var, array('name', 'type', 'tmp_name', 'error', 'size'))) {
      return $this->file_array[$var];
    }
    return false;
  }

  /**
   * get_file_array 
   * 
   * @access public
   * @return array
   */
  public function get_file_array() {
    return $this->file_array;
  }

}

?>
