<?php

if(!function_exists('get_called_class')) {
  function get_called_class() {
    $bt = debug_backtrace();
    $l = 0;
    do {
        $l++;
        $lines = file($bt[$l]['file']);
        $callerLine = $lines[$bt[$l]['line']-1];
        preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                   $callerLine,
                   $matches);
                  
       if ($matches[1] == 'self') {
               $line = $bt[$l]['line']-1;
               while ($line > 0 && strpos($lines[$line], 'class') === false) {
                   $line--;                  
               }
               preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
       }
    }
    while ($matches[1] == 'parent'  && $matches[1]);
    return $matches[1];
  }
}

array_walk(glob('./lib/*.php'),create_function('$v,$i', 'return require_once($v);')); 
array_walk(glob('./config/*.php'),create_function('$v,$i', 'return require_once($v);')); 
array_walk(glob('./models/*.php'),create_function('$v,$i', 'return require_once($v);')); 

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
