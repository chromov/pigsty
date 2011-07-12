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
  public function render($options = array()) {
    $default_options = array('prev' => "&lsaquo;", 'next' => "&rsaquo;");
    $options = array_merge($default_options, $options);
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

    $q_params = array();
    foreach($_GET as $g_param => $g_val) {
      if(($g_param != "page") && ($g_param != 'URI__')) {
        $q_params[] = "{$g_param}={$g_val}";
      }
    }
    if(sizeof($q_params) > 0) {
      $link_base = "/".$_GET['URI__']."?".join($q_params, "&")."&page=";
    } else {
      $link_base = "/".$_GET['URI__']."?page=";
    }

    $res = '<div class="pager">';
    if($this->current_page > 1) {
      $res .= '<span class="backward"><a href="'.$link_base.($this->current_page-1).'" title="Назад">'.$options['prev'].'</a></span>';
    }
    $res .= '<ul>';
    
    foreach ($links_parts as $index => $part) {
      foreach ($part as $page_num) {
        if($page_num == $this->current_page) {
          $res .= '<li class="current_page">'.$page_num.'</li>';
        } else {
          $res .= '<li><a href="'.$link_base.$page_num.'">'.$page_num.'</a></li>';
        }
      }
      if($index + 1 != sizeof($links_parts)) {
        $res .= '<span>...</span>';
      }
    }

    $res .= '</ul>';
    if($this->current_page < $this->total) {
      $res .= '<span class="forward"><a href="'.$link_base.($this->current_page+1).'" title="Вперед">'.$options['next'].'</a></span>';
    }
    $res .= '</div>';
    return $res;
  }

}

?>
