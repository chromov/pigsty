<?php

require_once('lib/phpmailer/class.phpmailer.php');
  
class PorkMailer extends PHPMailer {

  protected static $options = array('sendmail' => true, 'charset' => 'utf-8', 'encoding' => 'base64');

  public static function set_default_options($options = array()) {
    foreach($options as $key => $option) {
      self::$options[$key] = $option;
    }
  }

  public function __construct() {
    if(self::$options['sendmail']) {
      $this->IsSendmail();
    }
    $this->CharSet = self::$options['charset'];
    $this->Encoding = self::$options['encoding'];
    $this->body = array();
  }

  static public function deliver($method, $params = array()) {
    $class_name = get_called_class();
    $obj = new $class_name(true);
    call_user_func_array(array($obj, $method), $params);
    $html_body = $obj->prepare_template($method);
    $obj->MsgHTML($html_body);
    $obj->Send();
  }

  private function prepare_template($method) {
    $controller = Utils::caps_to_underscores(get_class($this));
    if(!is_file($file_path = Pigsty::doc_root()."mailer/views/{$controller}/{$method}.".I18n::get_locale().".html.php")) {
      $file_path = Pigsty::doc_root()."mailer/views/{$controller}/{$method}.html.php";
    }
    if (!file_exists($file_path)) {
      return "<strong>Can't find template <em>$file_path</em></strong><br/>\n";
    }
    if($this->body) {
      foreach($this->body as $key => $val) {
        $$key = $val;
      }
    }
    ob_start();
    require($file_path);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
  }

  public function subject($str) {
    $this->Subject = $str;
  }

  public function from($str) {
    $this->SetFrom($str);
  }

  public function recipients($rcpt) {
    if(is_string($rcpt)) {
      $this->AddAddress($rcpt);
    } elseif(is_array($rcpt)) {
      foreach($rcpt as $r) {
        $this->AddAddress($r);
      }
    }
  }

  public function body($vars) {
    $this->body = $vars;
  }

}

?>
