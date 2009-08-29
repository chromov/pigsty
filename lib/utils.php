<?php

class Utils {

  /**
   * classify 
   * 
   * @param string $class_name 
   * @static
   * @access public
   * @return string
   */
  public static function classify($class_name) {
    $capital = ucwords($class_name);
    if(class_exists($capital)) {
      return $capital;
    }
    $sin = ucwords(Inflect::singularize($class_name));
    if(class_exists($sin)) {
      return $sin;
    }
    return false;
  }

}

?>
