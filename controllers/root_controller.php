<?php

class RootController extends Controller {

  private $title = "";

  protected function before_filter() {
    session_start();
    if(!isset($_SESSION['started_at'])) {
      $_SESSION['started_at'] = time();
    }
  }

  protected function set_title($new_title) {
    $this->title = $new_title;
  }

  protected function get_title() {
    return $this->title;
  }

  protected function add_to_title($str) {
    $this->title = $str." - ".$this->title;
}

}

?>
