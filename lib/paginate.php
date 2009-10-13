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
   * window 
   * 
   * @var integer
   * @access public
   */
  public $window = 15;
  
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
    $wing = floor($this->window/2);
    
    if($this->window + 4 < $this->total){
      if((3+$wing < $this->current_page) && ($this->total - $wing - 2 > $this->current_page)) {
        $links_parts[0] = array(1);
        $links_parts[1] = range($this->current_page - $wing, $this->current_page + $wing);
        $links_parts[2] = array($this->total);
      } else {
        if(3+$wing < $this->current_page) {
          $links_parts[0] = array(1);
          if($this->total - $this->current_page < $wing) {
            $links_parts[1] = range($this->total - $this->window +1, $this->total);
          } else {
            $links_parts[1] = range($this->current_page - $wing, $this->total);
          }
        } else {
          if($this->current_page <= $wing) {
            $links_parts[0] = range(1, $this->window);
          } else {
            $links_parts[0] = range(1, $this->current_page + $wing);
          }
          $links_parts[1] = array($this->total);
        }
      }
    } else {
      $links_parts[0] = range(1, $this->total);
    }

    $link_base = "/".(strpos($_GET['URI__'], "?") === false ? $_GET['URI__']."?page=" : $_GET['URI__']."&page=");

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
        $res .= '<span>...</span>';
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
