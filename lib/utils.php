<?php

class Utils {

  /**
   * classify 
   * 
   * @param string $class_name 
   * @static
   * @access public
   * @return mixed
   */
  public static function classify($class_name) {
    $capital = ucwords($class_name);
    if(class_exists($capital)) {
      return $capital;
    }
    $first_caps = self::first_caps($class_name);
    $sin = Inflect::singularize($first_caps);
    if(class_exists($sin)) {
      return $sin;
    }
    return false;
  }

  /**
   * caps_to_underscores 
   * 
   * @param string $string 
   * @static
   * @access public
   * @return string
   */
  public static function caps_to_underscores($string) {
    return join('_', explode(' ', strtolower(trim(preg_replace('/[A-Z]/', ' $0', $string)))));
  }

  /**
   * first_caps 
   * 
   * @param string $input 
   * @static
   * @access public
   * @return string
   */
  public static function first_caps($input) {
    $parts = explode("_", $input);
    $first_caps = "";
    foreach($parts as $part) {
      $first_caps .= ucwords($part);
    }
    return $first_caps;
  }

  /**
   * slugify 
   * 
   * @param string $str 
   * @static
   * @access public
   * @return string
   */
  public static function slugify($str) {
    $str = I18n::transliterate($str);
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', "-", $str);
    return $str;
  }

}

?>
