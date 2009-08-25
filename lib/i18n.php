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
    $st = strtr($input, 
      "абвгдежзийклмнопрстуфыэАБВГДЕЖЗИЙКЛМНОПРСТУФЫЭ",
      "abvgdegziyklmnoprstufieABVGDEGZIYKLMNOPRSTUFIE"
    );
    $st = strtr($st, array(
      'ё'=>"yo",    'х'=>"h",  'ц'=>"ts",  'ч'=>"ch", 'ш'=>"sh",  
      'щ'=>"shch",  'ъ'=>'',   'ь'=>'',    'ю'=>"yu", 'я'=>"ya",
      'Ё'=>"Yo",    'Х'=>"H",  'Ц'=>"Ts",  'Ч'=>"Ch", 'Ш'=>"Sh",
      'Щ'=>"Shch",  'Ъ'=>'',   'Ь'=>'',    'Ю'=>"Yu", 'Я'=>"Ya",
    ));
    return $st;
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
    if (self::$locale == "") {
      return "Locale is not set";
    }
    if (!isset(self::$translations[self::$locale])) {
      return "";
    }
    $trans = self::$translations[self::$locale];
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
