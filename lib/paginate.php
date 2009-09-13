<?php

class Paginate {

  /**
   * collection 
   * 
   * @var array
   * @access public
   */
  public $collection = array();

  /**
   * current_page 
   * 
   * @var integer
   * @access private
   */
  private $current_page = 1;

  /**
   * total 
   * 
   * @var integer
   * @access private
   */
  public $total;
  
  /**
   * __construct 
   * 
   * @param array $collection 
   * @param integer $current_page 
   * @param integer $total 
   * @access public
   * @return void
   */
  public function __construct($collection, $current_page, $total) {
    $this->collection = $collection;
    $this->current_page = $current_page;
    $this->total = $total;
  }

  /**
   * render 
   * 
   * @access public
   * @return string
   */
  public function render() {
    $links_parts = array();
    
    //TODO split pages in three parts
    $links_parts[0] = range(1, $this->total);

    $link_base = strpos($_GET['URI__'], "?") === false ? $_GET['URI__']."?page=" : $_GET['URI__']."&page=";

    $res = '<div class="pager" style="margin: 15px 0; text-align:center">';
    if($this->current_page > 1) {
      $res .= '<span class="backward"><a href="'.$link_base.($this->current_page-1).'" title="Назад" style="font-size:16px;">&lsaquo;</a></span>';
    }
    $res .= '<ul style="list-style: none; display: inline; margin: 0">';
    
    foreach ($links_parts as $index => $part) {
      foreach ($part as $page_num) {
        if($page_num == $this->current_page) {
          $res .= '<li style="display: inline;padding: 2px 6px; border: 1px solid #aaa" class="current_page">'.$page_num.'</li>';
        } else {
          $res .= '<li style="display: inline;"><a href="'.$link_base.$page_num.'" style="padding: 2px 6px; border: 1px solid #eee">'.$page_num.'</a></li>';
        }
      }
      if($index + 1 != sizeof($links_parts)) {
        $res .= '<span>&0133;</span>';
      }
    }

    $res .= '</ul>';
    if($this->current_page < $this->total) {
      $res .= '<span class="forward"><a href="'.$link_base.($this->current_page+1).'" title="Вперед" style="font-size:16px;">&rsaquo;</a></span>';
    }
    $res .= '</div>';
    return $res;
  }

}

?>

