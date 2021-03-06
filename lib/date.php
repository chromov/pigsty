<?php

class Date {

  /**
   * humanize 
   * 
   * @param string $str_date 
   * @static
   * @access public
   * @return string
   */
  static public function humanize($str_date) {
    $date_arr = date_parse($str_date);
    $output = "";

    $year_type = array('small', 'related');
    if($date_arr['month'] && $date_arr['day']) {
      $output .= $date_arr['day']." ".I18n::get_month_name($date_arr['month'], array('small', 'related'));
    } elseif($date_arr['month']) {
      $output .= I18n::get_month_name($date_arr['month'], array('capital'));
    } else {
      $year_type = array('small');
    }
    if($date_arr['year']) {
      $output .= " ".$date_arr['year']." ".I18n::get_year_sign($year_type);
    }

    return $output;
  }

}

?>
