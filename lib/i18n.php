<?php

/**
 * I18n
 * Internationalization class
 */
class I18n {

  /**
   * translations 
   * 
   * @static
   * @var array
   * @access private
   */
  private static $translations = array();

  /**
   * fallbacks 
   * 
   * @static
   * @var array
   * @access private
   */
  private static $fallbacks = array();

  /**
   * locale 
   * 
   * @static
   * @var string
   * @access private
   */
  private static $locale = "";

  /**
   * default_locale 
   * 
   * @static
   * @var string
   * @access public
   */
  public static $default_locale = "ua";

  /**
   * active 
   * 
   * @static
   * @var boolean
   * @access private
   */
  private static $active = false;

  /**
   * set_translations 
   * 
   * @param array $translations 
   * @static
   * @access public
   * @return void
   */
  public static function set_translations($translations) {
    self::$translations = $translations;
  }

  /**
   * set_fallbacks 
   * 
   * @param array $fallbacks 
   * @access public
   * @return void
   */
  public function set_fallbacks($fallbacks) {
    self::$fallbacks = $fallbacks;
  }

  /**
   * get_locale 
   * 
   * @static
   * @access public
   * @return string
   */
  public static function get_locale() {
    if(self::$locale == '') {
      return self::$default_locale;
    }
    return self::$locale;
  }  

  /**
   * set_locale 
   * 
   * @param string $new_locale 
   * @static
   * @access public
   * @return void
   */
  public static function set_locale($new_locale) {
    self::$locale = $new_locale;
  }

  /**
   * get_active 
   * 
   * @static
   * @access public
   * @return boolean
   */
  public static function get_active() {
    return self::$active;
  }

  /**
   * set_active 
   * 
   * @param boolean $active 
   * @static
   * @access public
   * @return void
   */
  public static function set_active($active) {
    self::$active = $active;
  }

  /**
   * transliterate 
   * 
   * @param string $input 
   * @static
   * @access public
   * @return string
   */
  public static function transliterate($input) {
    $table = array(
      'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
      'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K',
      'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
      'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
      'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'CSH', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
      'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'І' => "I", 'Ї' => "I", 'Є' => "E",

      'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
      'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k',
      'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
      'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
      'ч' => 'ch', 'ш' => 'sh', 'щ' => 'csh', 'ь' => '', 'ы' => 'y', 'ъ' => '',
      'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'і' => "i", 'ї' => "i", 'є' => "e"
    );

    $output = str_replace(
        array_keys($table),
        array_values($table),$input
    );

    return $output;
  } 

  /**
   * plural 
   * 
   * @param integer $n 
   * @param string $f1 
   * @param string $f2 
   * @param string $f3 
   * @static
   * @access public
   * @return string
   */
  public static function plural($n, $f1, $f2, $f3) {
    if(($n % 10 == 1) && ($n % 100 != 11)) return $f1;
    if(in_array($n % 10, array(2,3,4)) && !in_array($n % 100, array(12,13,14))) return $f2;
    if(($n % 10 == 0) || in_array($n % 10, array(5,6,7,8,9)) || in_array($n % 100, array(11,12,13,14))) return $f3;
    return $f1;
  }

  /**
   * tr 
   * 
   * @param string $subject 
   * @static
   * @access public
   * @return string
   */
  public static function tr($subject) {
    if (!isset(self::$translations[self::get_locale()])) {
      return "";
    }
    $trans = self::$translations[self::get_locale()];
    if (isset($trans[$subject])) {
      return $trans[$subject];
    } else {
      return "";
    }
  }

  /**
   * fallback 
   * 
   * @param string $locale 
   * @static
   * @access public
   * @return string
   */
  public static function fallback($locale) {
    if(isset(self::$fallbacks[$locale])) {
      return self::$fallbacks[$locale];
    } else {
      return '';
    }
  }

}
?>
