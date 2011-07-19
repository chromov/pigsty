<?php

require_once "lib/pork.dbobject/class.settings.php";
require_once "lib/pork.dbobject/class.dbconnection.php";
require_once "lib/pork.dbobject/class.dbobject.php";
require_once "lib/pork.dbobject/class.logger.php";

class PorkRecord extends dbObject {

  private $pre_saved_relations = array();

  /**
   * translated_fields 
   * 
   * @var array
   * @access private
   */
  private $translated_fields = array();

  /**
   * parent_object 
   * 
   * @var mixed
   * @access private
   */
  private $parent_object;


  /**
   * lazy_model_attributes 
   * 
   * @var array
   * @access private
   */
  private $lazy_model_attributes = array();

  /**
   * validations 
   * 
   * @var array
   * @access private
   */
  private $validations = array();

  /**
   * errors 
   * 
   * @var array
   * @access private
   */
  private $errors = array();

  /**
   * lazy_translation_attributes 
   * 
   * @var array
   * @access private
   */
  private $lazy_translation_attributes = array();

  /**
   * file_observers 
   * 
   * @var array
   * @access private
   */
  protected $file_observers = array();

  /**
   * before_save_observers
   * 
   * @var array
   * @access protected
   */
  protected $before_save_observers = array();

  /**
   * after_save_observers 
   * 
   * @var array
   * @access protected
   */
  protected $after_save_observers = array();

  /**
   * debug_mode 
   * 
   * @var boolean
   * @access public
   */
  public $debug_mode = false;

  /**
   * set_parent_object 
   * 
   * @param mixed $parent 
   * @access public
   * @return void
   */
  public function set_parent_object($parent) {
    $this->parent_object = $parent;
  }

  /**
   * get_parent_object 
   * 
   * @access public
   * @return PorkRecord
   */
  public function get_parent_object() {
    return $this->parent_object;
  }

  /**
   * get_errors 
   * 
   * @access public
   * @return array
   */
  public function get_errors() {
    return $this->errors;
  }

  /**
   * hasProperty
   * 
   * @param string $property 
   * @access public
   * @return boolean
   */
  public function hasProperty($property) { 
    if(array_search($property, $this->translated_fields) !== false) return true;
    if(array_key_exists($property, $this->databaseValues) !== false) return true;
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
      $filters = isset($args[0]) ? $args[0] : array();
      $extra = isset($args[1]) ? $args[1] : array();
      $just_these = isset($args[2]) ? $args[2] : array();
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
    } elseif($parent = $this->get_parent_object()) {
      return $parent->__call($name, $args);
    } else {
      return false;
    }
  }

  /**
   * __set 
   * 
   * @param mixed $property 
   * @param mixed $value 
   * @access public
   * @return void
   */
  public function __set($property, $value) {
    if(is_array($value)) {
      if(isset($value['year']) || isset($value['min'])) {
        $value = sprintf("%04d-%02d-%02d %02d:%02d", $value['year'], $value['month'], $value['day'], $value['hours'], $value['min']);
      } elseif(($class_name = Utils::classify($property)) && array_key_exists($class_name, $this->relations)) {
        $this->pre_saved_relations[$class_name] = $value;
        return;
      } else {
        $value = implode(', ', $value);
      }
    }
    if(is_object($value) && (get_class($value) == "File")) { 
      if(array_key_exists($property, $this->file_observers)) {
        call_user_func(array($this, $this->file_observers[$property]), $value);
      } elseif(($parent = $this->get_parent_object()) && (array_key_exists($property, $parent->file_observers))) {
        $parent->__set($property, $value);
      }
      return;
    }
    if($this->hasProperty($property)) {
      parent::__set($property, $value);
    } elseif(($this->parent_object) && ($this->parent_object->hasProperty($property))) {
      $this->parent_object->__set($property, $value);
    } else {
			Logger::Trace("Tried to set a non-dbObject property for ".get_class($this), $property, $value);
    }
  }


  public function __get($property) { 
    if($this->hasProperty($property)) {
      if($this->has_lazy_attribute($property)) {
        $this->load_tranlations($property);
      }
      $pg = parent::__get($property);
      return $pg;
    } elseif($property != 'ID' && $this->parent_object) {
      return $this->parent_object->__get($property);
    }
    return false;
  }

  public function __tostring() {
    if(!$this->is_new_record()) {
      return (string) $this->ID;
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
    if(is_numeric($filters)) {
      $filters = array($obj->databaseInfo->primary => $filters );
      $results = $obj->find_by_class_name($class_name, $filters, $extra, $just_these);
      return $results[0];
    }
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
    return($obj->find_count_by_class_name($class_name, $filters, $extra, $justThese));
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
    $extra[] = "limit 1";
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
      foreach($results as $key => $_res) {
        if($_res->hasProperty('type') && class_exists($_res->type)) {
          $child = $_res->find_sti_child();
          $child->parent_object = $_res;
          $child->load_tranlations();
          $results[$key] = $child;
        } else {
          if($_res->parent_object) {
            $_res->parent_object = $_res->find_sti_parent();
          }
          $_res->load_tranlations();
        }
      }
    }
		return($results != false ? $results : array());
  }

  /**
   * reload 
   * 
   * @access public
   * @return void
   */
  public function reload() {
    $filters = array($this->databaseInfo->primary => $this->ID);
    $builder = new QueryBuilder(get_class($this), $filters);
    $input = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll($builder->buildQuery(), 'assoc');
    $this->import($input[0]);
    $this->translated_fields = array();
    $this->load_tranlations();
  }

  /**
   * find_sti_child 
   * 
   * @access private
   * @return mixed
   */
  private function find_sti_child() {
    $builder = new QueryBuilder($this->type, array('parent_id' => $this->ID));
    $input = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll($builder->buildQuery(), 'assoc');
    $results = dbObject::importArray($this->type, $input);
    return ((is_array($results) && (sizeof($results) > 0)) ? $results[0] : false);
  }

  /**
   * find_sti_parent 
   * 
   * @access private
   * @return mixed
   */
  private function find_sti_parent() {
    $builder = new QueryBuilder(get_class($this->parent_object), array('type' => get_class($this), $this->parent_object->databaseInfo->primary => $this->parent_id));
    $input = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll($builder->buildQuery(), 'assoc');
    $results = dbObject::importArray(get_class($this->parent_object), $input);
    return ((is_array($results) && (sizeof($results) > 0)) ? $results[0] : false);
  }

  /**
   * pid 
   *
   * returns id of itself or of the parent object if any
   * 
   * @access public
   * @return integer
   */
  public function pid() {
    if($this->parent_object) {
      return $this->parent_object->pid();
    }
    return $this->ID;
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

  /**
   * translates_field 
   * 
   * @param string $field 
   * @access public
   * @return boolean
   */
  public function translates_field($field) {
    return(array_search($field, $this->translated_fields) !== false);
  }


  public function __setupDatabase($table, $fields, $primarykey, $id=false, $connection='Database') {
    parent::__setupDatabase($table, $fields, $primarykey, $id, $connection);
    $this_class = get_class($this);
    if((($parent_class = get_parent_class($this_class)) != "PorkRecord") && ($this_class != "PorkRecord")) {
      $this->parent_object = new $parent_class;
    }
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
    $count = $obj->find_count_by_class_name($class_name, $filters);
    return (new Paginate($collection, $page, ceil($count/$per_page)));
  }

  /**
   * paginate_collection 
   * 
   * @param int $page 
   * @param int $per_page 
   * @param array $collection 
   * @static
   * @access public
   * @return Paginate
   */
  static public function paginate_collection($page = NULL, $per_page = 20, $collection = array()) {
    if($page == NULL) {
      $page = 1;
    }
    $offset = ($page-1)*$per_page;
    $count = sizeof($collection);
    $collection = array_slice($collection, $offset, $per_page);
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
   * is_new_record 
   * 
   * @access public
   * @return boolean
   */
  public function is_new_record() {
    return $this->databaseInfo->ID == false;
  }

  /**
   * resource 
   * 
   * @access public
   * @return string
   */
  public function resource() {
    return Inflect::singularize($this->databaseInfo->table);
  }

  /**
   * resources 
   * 
   * @access public
   * @return string
   */
  public function resources() {
    return $this->databaseInfo->table;
  }

  /**
   * save 
   * 
   * @access public
   * @return mixed
   */
  public function save() {
    $valid = $this->validate();
    if(!$valid) {
      return false;
    }
    foreach ($this->before_save_observers as $bs) {
      call_user_func(array($this, $bs));
    }
    $ret = false;
		if($this->databaseInfo->ID == false) { // it's a new record for the db
      $this->touch_datetime_field('created_at');
      $this->touch_datetime_field('updated_at');
    } elseif ($this->changedValues != false) { // otherwise just build the update query
      $this->touch_datetime_field('updated_at');
    }
    if (($this->changedValues != false) && (sizeof($this->changedValues) > 0)) {
      $all_valid = true;
      if($this->parent_object) {
        $this->parent_object->type = get_class($this);
        if (!$this->parent_object->save()) {
          $all_valid = false;
        }
      }

      if($all_valid) {
        $changed_translations = array();
        if(($this->translated_fields) && ($changed_translations = array_intersect_key($this->changedValues, array_flip($this->translated_fields)))) {
          $this->changedValues = array_diff_key($this-> changedValues, $changed_translations);
        }
        if($this->parent_object) {
          $this->parent_id = $this->parent_object->databaseInfo->ID;
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
        $ret = $ret_id;
      } else {
        return false;
      }
    } elseif($this->databaseInfo->ID != false) {
      if($this->parent_object && $this->parent_object->save()) {
        $ret = true;
      }
    }
    if($ret) {
      foreach ($this->after_save_observers as $as) {
        call_user_func(array($this, $as));
      }
      foreach($this->pre_saved_relations as $class_name => $val) {
        if($this->relations[$class_name]->relationType == RELATION_NOT_ANALYZED) {
					$this->analyzeRelations();
        }
        switch($this->relations[$class_name]->relationType) {
        case RELATION_MANY:
					$conn_class = $this->relations[$class_name]->connectorClass;
          $object = new $class_name;
          $results = $this->find_by_class_name($class_name, array(get_class($this) => array("ID" => $this->ID)), array(), array($object->databaseInfo->primary));
          $cur_ids = array_map(create_function('$obj', 'return $obj->ID;'), $results);
          if(is_array($val)) {
            $ids_to_del = array_diff($cur_ids, $val);
            $ids_to_add = array_diff($val, $cur_ids);
            foreach($ids_to_del as $del_id) {
              $this->disconnect($class_name, $del_id);
            }
            foreach($ids_to_add as $rel) {
              if(is_string($rel)) {
                $connector = new $conn_class(false);
                $property = $connector->databaseInfo->fields[$this->databaseInfo->primary];
                $connector->$property = $this->databaseInfo->ID;
                $property = $connector->databaseInfo->fields[$object->databaseInfo->primary];
                $connector->$property = $rel;
                $connector->save();
              }
            }
          }
          break;
        }
      }
      return $ret;
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
      if((array_search($property, $this->translated_fields) !== false) && I18n::get_active() && ((I18n::get_locale() != I18n::$default_locale) && !I18n::get_localized_slug() )) {
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

  /**
   * has_translation 
   * 
   * @param string $locale 
   * @access public
   * @return void
   */
  public function has_translation($locale = "") {
    $and_locale = "";
    if($locale != "") { 
      $and_locale = " and locale = '{$locale}'";
    }
    $values = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll("select * from {$this->databaseInfo->table}_translations where id_parent = {$this->databaseInfo->ID}".$and_locale, 'assoc');
    return(($values != false && sizeof($values) > 0) ? true : false);
  }

  /**
   * update_translation 
   * 
   * @param array $new_fields 
   * @param string $locale 
   * @access public
   * @return void
   */
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

  /**
   * add_translation 
   * 
   * @param array $new_fields 
   * @param string $locale 
   * @access public
   * @return void
   */
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
  protected function load_tranlations($lazy_attr = NULL) {
    if($this->parent_object) {
      $this->parent_object->load_tranlations($lazy_attr);
    }
    if(sizeof($this->translated_fields) == 0) {
      return;
    }
    $valid_fields = array();
    if($lazy_attr) {
      $valid_fields[] = $lazy_attr;
    } else {
      if(sizeof($this->lazy_translation_attributes) > 0) {
        $valid_fields = array_diff($this->translated_fields, $this->lazy_translation_attributes);
      } else {
        $valid_fields = $this->translated_fields;
      }
    }
    $fieldnames = implode(",", $valid_fields);
    $values = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll("select locale, {$fieldnames} from {$this->databaseInfo->table}_translations where id_parent = {$this->databaseInfo->ID}", 'assoc');

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
        foreach ($valid_fields as $field) {
          $translated_fields[$field] = $desired_row[$field];
        }
      }
      $this->databaseValues = array_merge($this->databaseValues, $translated_fields);
    }
  }

  /**
   * lazy_attributes 
   * 
   * @param array $attrs 
   * @access protected
   * @return void
   */
  protected function lazy_attributes($attrs) {
    foreach($attrs as $attr) {
      if($this->translates_field($attr)) {
        $this->lazy_translation_attributes[] = $attr;
      } else {
        $this->lazy_attributes[] = $attr;
      }
    }
  }

  private function has_lazy_attribute($property) {
    return((array_search($property, $this->lazy_model_attributes) !== false) || (array_search($property, $this->lazy_translation_attributes) !== false));
  }


  /**
   * validate 
   * 
   * @access private
   * @return boolean
   */
  private function validate() {
    if(sizeof($this->validations) > 0) {
      $this->errors = array();
      foreach($this->validations as $property => $whens) {
        $value = $this->$property;
        $callbacks = $whens['all'];
        if($this->is_new_record()) {
          $callbacks = array_merge($callbacks, $whens['create']);
        } else {
          $callbacks = array_merge($callbacks, $whens['update']);
        }
        foreach($callbacks as $callback){
          $msg = $callback($value);
          if($msg != '') {
            if(!isset($this->errors[$property])) {
              $this->errors[$property] = array();
            }
            $this->errors[$property][] = $msg;
          }
        }
      }
      if(sizeof($this->errors) > 0) {
        return false;
      }
    }
    return true;
  }

  public function is_valid() {
    return $this->validate(); 
  }

  /**
   * validates_presence_of 
   * 
   * @param string $field_name 
   * @param string $msg 
   * @access protected
   * @return void
   */
  protected function validates_presence_of($field_name, $options = array()) {
    $def_options = array("msg" => "Не может быть пустым", "on" => "all");
    $options = array_merge($def_options, $options);
    $msg = $options["msg"];
    $callback = false;
    if($this->hasProperty($field_name)) {
      $callback = create_function('$value', 'if(empty($value)) return "'.$msg.'"; else return "";');
    }
    if(array_key_exists($field_name, $this->file_observers)){
      $callback = create_function('$value', 'if(empty($value)||($value->error != "0")) return "'.$msg.'"; else return "";');
    }
    if($callback) {
      if(!isset($this->validations[$field_name])) {
        $this->validations[$field_name] = array("create" => array(), "update" => array(), "all" => array());
      }
      $this->validations[$field_name][$options["on"]][] = $callback;
    }
  }

  /**
   * destroy_translations 
   * 
   * @access protected
   * @return void
   */
  protected function destroy_translations() {
    dbConnection::getInstance($this->databaseInfo->connection)->query("delete from {$this->databaseInfo->table}_translations where id_parent = {$this->databaseInfo->ID}");
  }

  /**
   * destroy_sti_parent 
   * 
   * @access protected
   * @return void
   */
  protected function destroy_sti_parent() {
    if($this->parent_object) {
      $this->parent_object->destroy();
    }
  }

  /**
   * destroy 
   * 
   * @access public
   * @return void
   */
  public function destroy() {
    if($this->has_translation()) {
      $this->destroy_translations();
    }
    $this->destroy_sti_parent();
    $this->deleteYourSelf();
  }


  /**
   * CALLBACKS
   */

  /**
   * after_save 
   * 
   * @param mixed $callback 
   * @access protected
   * @return void
   */
  protected function after_save($callback) {
    if(is_array($callback)) {
      $this->after_save_observers = array_merge($this->after_save_observers, $callback);
    } elseif(is_string($callback)) {
      $this->after_save_observers[] = $callback;
    }
  }

  /**
   * before_save 
   * 
   * @param mixed $callback 
   * @access protected
   * @return void
   */
  protected function before_save($callback) {
    if(is_array($callback)) {
      $this->before_save_observers = array_merge($this->before_save_observers, $callback);
    } elseif(is_string($callback)) {
      $this->before_save_observers[] = $callback;
    }
  }

  /**
   * add_file_observer 
   * 
   * @param string $property 
   * @param string $callback 
   * @access protected
   * @return void
   */
  protected function add_file_observer($property, $callback) {
    $this->file_observers[$property] = $callback;
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
