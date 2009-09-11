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
    $parts = explode("_", $class_name);
    $first_caps = "";
    foreach($parts as $part) {
      $first_caps .= ucwords($part);
    }
    $sin = ucwords(Inflect::singularize($first_caps));
    if(class_exists($sin)) {
      return $sin;
    }
    return false;
  }


  public static function slugify($str) {
    $str = I18n::transliterate($str);
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', "-", $str);
    return $str;
  }

}

?>
