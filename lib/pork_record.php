<?php

require_once "lib/pork.dbobject/class.settings.php";
require_once "lib/pork.dbobject/class.dbconnection.php";
require_once "lib/pork.dbobject/class.dbobject.php";
require_once "lib/pork.dbobject/class.logger.php";

class PorkRecord extends dbObject {

  /**
   * translated_fields 
   * 
   * @var array
   * @access private
   */
  private $translated_fields = array();

  /**
   * debug_mode 
   * 
   * @var boolean
   * @access public
   */
  public $debug_mode = false;

  /**
   * hasProperty
   * 
   * @param string $property 
   * @access public
   * @return boolean
   */
  public function hasProperty($property) { 
    if(array_search($property, $this->translated_fields) !== false) return true;
    return parent::hasProperty($property);
  }

  /**
   * fieldForProperty 
   * 
   * @param string $property 
   * @access public
   * @return mixed
   */
	public function fieldForProperty($property) {
    if(array_search($property, $this->translated_fields) !== false) {
      return $property;
    }
    return parent::fieldForProperty($property);
  }

  /**
   * overloaded __call method handles model relations
   */
  public function __call($name, $args) {
    $class_name = Utils::classify($name);
    if($class_name && array_key_exists($class_name, $this->relations)) {
      $filters = $args[0] ? $args[0] : array();
      $extra = $args[1] ? $args[1] : array();
      $just_these = $args[2] ? $args[2] : array();
      if($this->ID != false) {
        $filters["ID"] = $this->ID;
        $filters = array(get_class($this) => $filters);	
      }
      $results = $this->find_by_class_name($class_name, $filters, $extra, $just_these);
      $obj = new $class_name;

      $this->analyzeRelations();
      switch($this->relations[$class_name]->relationType) {
      case RELATION_SINGLE:
        return(sizeof($results) > 0 ? $results[0] : NULL);
        break;
      case RELATION_FOREIGN:
        if(array_key_exists($this->databaseInfo->primary, $obj->databaseInfo->fields)) {
          return(sizeof($results) > 0 ? $results : array());
        } elseif(array_key_exists($obj->databaseInfo->primary, $this->databaseInfo->fields)) {
          return(sizeof($results) > 0 ? $results[0] : NULL);
        }
        break;
      case RELATION_MANY:
        return(sizeof($results) > 0 ? $results : array());
        break;
      default:
        return false;
        break;
      }
    } else {
      return false;
    }
  }

  /**
   * __destruct 
   * 
   * @access public
   * @return void
   */
  public function __destruct() {
    
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
    $class_name = get_called_class();
    $obj = new $class_name();
    return($obj->find_by_class_name($class_name, $filters, $extra, $just_these));
  }

  /**
   * find_count 
   * 
   * @param array $filters 
   * @param array $extra 
   * @param array $justThese 
   * @access public
   * @return integer
   */
  static public function find_count($filters=array(), $extra=array(), $justThese= array()) {
    $class_name = get_called_class();
    $obj = new $class_name();
    return($obj->find_count_by_class_name($class_name, $filters, $extra, $just_these));
  }

  /**
   * find_first 
   * 
   * @param array $filters 
   * @param array $extra 
   * @param array $just_these 
   * @static
   * @access public
   * @return mixed
   */
  static public function find_first($filters=array(), $extra=array(), $just_these=array()) {
    $class_name = get_called_class();
    $obj = new $class_name();
    $results = $obj->find_by_class_name($class_name, $filters, $extra, $just_these);

    if (sizeof($results) > 0) {
      return $results[0];
    }
    return NULL;
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
    if($this->debug_mode) {
      var_dump($builder->buildQuery());
    }
    if($results = dbObject::importArray($className, $input)) {
      foreach($results as $_res) {
        $_res->load_tranlations();
      }
    }
		return($results != false ? $results : array());
  }

  /**
   * find_count_by_class_name 
   * 
   * @param string $className 
   * @param array $filters 
   * @param array $extra 
   * @param array $justThese 
   * @access public
   * @return integer
   */
  public function find_count_by_class_name($className, $filters=array(), $extra=array(), $justThese=array()) {
		$builder = new QueryBuilder($className, $filters, $extra, $justThese);
    if($this->debug_mode) {
      var_dump($builder->buildQuery());
    }
		return ( $builder->getCount());
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


  public function __setupDatabase($table, $fields, $primarykey, $id=false, $connection='Database') {
    parent::__setupDatabase($table, $fields, $primarykey, $id, $connection);
    if($id) $this->__init();
  }

  /**
   * __init 
   * 
   * @access private
   * @return void
   */
  private function __init() {
    if($this->databaseInfo->ID != false) {
      $this->load_tranlations();
		} 
  }

  /**
   * paginate 
   * 
   * @param int $page 
   * @param int $per_page 
   * @param array $filters 
   * @param array $extra 
   * @param array $just_these 
   * @static
   * @access public
   * @return string
   */
  static public function paginate($page = NULL, $per_page = 20, $filters=array(), $extra=array(), $just_these=array()) {
    $class_name = get_called_class();
    $obj = new $class_name();

    if($page == NULL) {
      $page = 1;
    }
    $offset = ($page-1)*$per_page;
    $collection = $obj->find_by_class_name($class_name, $filters, array_merge($extra, array("limit {$offset}, {$per_page}")), $just_these);
    $count = $obj->find_count_by_class_name($class_name);
    return (new Paginate($collection, $page, ceil($count/$per_page)));
  }



  /**
   * load 
   *
   * TODO it should store only changed values
   * 
   * @param array $params 
   * @access public
   * @return mixed
   */
  public function load($params) {
    foreach($params as $key=>$val) {
			$this->$key = $val; 
		} 
    return $this;
  }

  /**
   * save 
   * 
   * @access public
   * @return mixed
   */
  public function save() {
		if(sizeof($this->changedValues) > 0 && $this->databaseInfo->ID == false) { // it's a new record for the db
      $this->touch_datetime_field('created_at');
      $this->touch_datetime_field('updated_at');
    } elseif ($this->changedValues != false) { // otherwise just build the update query
      $this->touch_datetime_field('updated_at');
    }
    if (($this->changedValues != false) && (sizeof($this->changedValues) > 0)) {
      $changed_translations = array();
      if(($this->translated_fields) && ($changed_translations = array_intersect_key($this->changedValues, array_flip($this->translated_fields)))) {
        $this->changedValues = array_diff_key($this-> changedValues, $changed_translations);
      }
      if($ret_id = parent::save()) {
        if(sizeof($changed_translations) > 0) {
          if($this->has_translation(I18n::get_locale())) {
            $this->update_translation($changed_translations, I18n::get_locale());
          } else {
            $this->add_translation($changed_translations, I18n::get_locale());
          }
        }
      }
      return $ret_id;
    }
    return false;
  }

  /**
   * touch_datetime_field 
   * 
   * @param string $property 
   * @access public
   * @return void
   */
  public function touch_datetime_field($property) {
    if($this->hasProperty($property) && !array_key_exists($this->fieldForProperty($property), $this->changedValues)) {
			$this->changedValues[$this->fieldForProperty($property)] = date("Y-m-d H:i:s");
    }
  }

  /**
   * touch_slug 
   * 
   * @param string $property 
   * @param string $slug 
   * @access public
   * @return void
   */
  public function touch_slug($property='name', $slug='slug') {
    if($this->hasProperty($property) && $this->hasProperty($slug)) {
      if((array_search($property, $this->translated_fields) !== false) && I18n::get_active() && (I18n::get_locale() != I18n::$default_locale)) {
        return $this;
      }
      $r_slug = Utils::slugify($this->$property);
      $iter = 1;

      $filter = array();
      if($this->databaseInfo->ID !== false) {
        $filter = array("{$this->databaseInfo->primary} != {$this->databaseInfo->ID}");
      }
      while (sizeof($this->find_by_class_name(get_class($this), array_merge($filter,array($slug => $r_slug)))) > 0) {
        $r_slug = Utils::slugify($this->$property)."-".$iter; 
        $iter++;
        if($iter > 5) {
          $r_slug = md5(time());
        }
      }

      $this->changedValues[$this->fieldForProperty($slug)] = $r_slug;
    }
    return $this;
  }

  public function has_translation($locale) {
    $values = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll("select * from {$this->databaseInfo->table}_translations where id_parent = {$this->databaseInfo->ID} and locale = '{$locale}'", 'assoc');
    return(($values != false && sizeof($values) > 0) ? true : false);
  }

  public function update_translation($new_fields, $locale) {
    $updateQuery = "";
    $new_fields['updated_at'] = date("Y-m-d H:i:s");
    foreach ($new_fields as $property=>$value) {
      $updateQuery .= ($updateQuery != '') ? ', ' : '';
      $updateQuery .= ($value != '') ? " {$property} = '".dbConnection::getInstance($this->databaseInfo->connection)->escapeValue($value)."'" : "{$property} = NULL";
      $this->databaseValues[$property] = $this->changedValues[$property];
    }
    dbConnection::getInstance($this->databaseInfo->connection)->query("update {$this->databaseInfo->table}_translations set {$updateQuery} where id_parent = {$this->databaseInfo->ID} and locale = '{$locale}'");   
  }

  public function add_translation($new_fields, $locale) {
    $insertValues = "";
    $new_fields['locale'] = $locale;
    $new_fields['id_parent'] = $this->databaseInfo->ID;
    $new_fields['created_at'] = date("Y-m-d H:i:s");
    $new_fields['updated_at'] = $new_fields['created_at'];
    $insertfields = implode(",", array_keys($new_fields));
    foreach ($new_fields as $property=>$value) {
      $insertValues .= ($insertValues != '') ? ', ' : '';
      $insertValues .= "'".dbConnection::getInstance($this->databaseInfo->connection)->escapeValue($value)."'";
      $this->databaseValues[$property] = $this->changedValues[$property];
    }
    dbConnection::getInstance($this->databaseInfo->connection)->query("insert into {$this->databaseInfo->table}_translations ({$insertfields}) values ($insertValues);");
  }

  /**
   * load_tranlations 
   * 
   * @access private
   * @return void
   */
  private function load_tranlations() {
    if(sizeof($this->translated_fields) == 0) {
      return;
    }
    $fieldnames = implode(",", array_keys($this->translated_fields));
    $values = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll("select * from {$this->databaseInfo->table}_translations where id_parent = {$this->databaseInfo->ID}", 'assoc');

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
        foreach ($this->translated_fields as $field) {
          $translated_fields[$field] = $desired_row[$field];
        }
      }
      $this->databaseValues = array_merge($this->databaseValues, $translated_fields);
    }
  }

  /**
   * PERMISSIONS
   */

  public static function can_be_created() {
    return false;
  }

  public function can_be_edited($field_name="") {
    return false;
  }

  public function can_be_viewed($field_name="") {
    return true;
  }

  public function can_be_destroyed() {
    return false;
  }


}

?>
