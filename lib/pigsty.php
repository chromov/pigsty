<?php

class Pigsty {

  /**
   * doc_root_val 
   * 
   * @static
   * @var string
   * @access public
   */
  public static $doc_root_val = "";

  /**
   * doc_root 
   * 
   * @static
   * @access public
   * @return string
   */
  public static function doc_root() {
    if(self::$doc_root_val == "") {
      $base = $_SERVER['DOCUMENT_ROOT']."/";
      $base = str_replace('//', '/', $base);
      self::$doc_root_val = $base;
    }
    return self::$doc_root_val;
  }

}

?>
