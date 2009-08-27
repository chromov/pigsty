<?php

require_once "lib/pork.dbobject/class.settings.php";
require_once "lib/pork.dbobject/class.dbconnection.php";
require_once "lib/pork.dbobject/class.logger.php";
require_once "lib/pork.dbobject/class.dbobject.php";

class PorkRecord extends dbObject {

  /**
   * translated_fields 
   * 
   * @var array
   * @access private
   */
  private $translated_fields = array();

  /**
   * overloaded __call method handles model relations
   */
  public function __call($name, $args) {
    $class_name = $this->classify($name);
    if($class_name && array_key_exists($class_name, $this->relations)) {
      $filters = $args[0];
      if($this->ID != false) {
        $filters["ID"] = $this->ID;
        $filters = array(get_class($this) => $filters);	
      }
      $this->find_by_class_name($class_name, $filters, $args[1], $args[2])
    } else {
      throw new Exception("No relation $name in model ".get_class($this));
    }
  }

  /**
   * find 
   * 
   * @param array $filters 
   * @param array $extra 
   * @param array $just_these 
   * @static
   * @access public
   * @return mixed
   */
  static public function find($filters=array(), $extra=array(), $just_these=array()) {
    $class_name = __CLASS__;
    $obj = new $class_name();
    return($obj->find_by_class_name($class_name, $filters, $extra, $just_these));
  }

  /**
   * find_by_class_name 
   * 
   * @param string $className 
   * @param array $filters 
   * @param array $extra 
   * @param array $justThese 
   * @access public
   * @return mixed
   */
  public function find_by_class_name($className, $filters=array(), $extra=array(), $justThese=array()) {
		$builder = new QueryBuilder($className, $filters, $extra, $justThese);
		$input = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll($builder->buildQuery(), 'assoc');
		return(dbObject::importArray($className, $input));
  }

  /**
   * translates 
   * 
   * @param array $fields 
   * @access public
   * @return void
   */
  public function translates($fields) {
    $this->translated_fields = $fields;
  }

  /**
   * __init 
   * 
   * @access private
   * @return void
   */
  private function __init() {
    if($this->databaseInfo->ID != false) {
			$fieldnames = implode(",", array_keys($this->databaseInfo->fields));
			$input = dbConnection::getInstance($this->databaseInfo->connection)->fetchRow("select {$fieldnames} from {$this->databaseInfo->table} where {$this->databaseInfo->primary} = {$this->databaseInfo->ID}", 'assoc');
			$this->import($input);
      if (sizeof($this->translated_fields > 0)) {
        $this->load_tranlations();
      }
		} 
  }

  /**
   * load_tranlations 
   * 
   * @access private
   * @return void
   */
  private function load_tranlations() {
    $fieldnames = implode(",", array_keys($this->translated_fields));
    $values = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll("select * from {$this->databaseInfo->table}_translations where parent_id = {$this->databaseInfo->ID}", 'assoc');

    if($values != false && sizeof($values) > 0) {
      foreach ($values as $row) {
        $translations[$row['locale']] = $row;
      }
      $locale = I18n::get_locale();
      while(($locale != "") && !isset($translations[$locale])) {
        $locale = I18n::fallback($locale);
      }
      $translated_fields = array();
      if ($locale != "") {
        $desired_row = $translations[$locale];
        foreach ($fieldnames as $field) {
          $translated_fields[$field] = $desired_row[$field];
        }
      }
      $this->databaseValues = array_merge($this->databaseValues, $translated_fields);
    }
  }

  public static function classify($class_name) {
    $capital = ucwords($class_name);
    if(class_exists($capital)) {
      return $capital;
    }
    $sin = ucwords(Inflect::singularize($class_name));
    if(class_exists($sin)) {
      return $sin;
    }
    return false;
  }

}

?>
