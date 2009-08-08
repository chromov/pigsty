<?php
/**
 * 
 *	Pork.dObject version 1.3.1 
 *	By Jelle Ursem
 *	see http://code.google.com/p/pork-dbobject/ for more info
 *	
 */

define('RELATION_SINGLE', 'RELATION_SINGLE');
define('RELATION_FOREIGN', 'RELATION_FOREIGN');
define('RELATION_MANY', 'RELATION_MANY');
define('RELATION_NOT_RECOGNIZED', 'RELATION_NOT_RECOGNIZED');
define('RELATION_NOT_ANALYZED', 'RELATION_NOT_ANALYZED');
define('RELATION_CUSTOM', 'RELATION_CUSTOM');

/**
 *  dbObject
 * 
 *	A tiny (500 lines) but powerful hot-pluggable OR-mapper/Active Record implementation for PHP5. 
 *	It automatically recognizes different types of relations in your database by matching primary keys and takes care of most of your SQL queries.
 * 
 * @package Pork
 * @author Jelle Ursem
 * @copyright Belfabriek 2009
 * @version 1.3.1
 * @access public
 */
class dbObject
{
	var $databaseInfo, $databaseValues, $changedValues, $relations, $orderProperty, $orderDirection, $db;

	/** 
	 *	This is  the function you use in the constructor of your objects to map fields to the database 
	 *	@param string $table the database table to hook this class to
	 *	@param array $fields array of fields/property mappings to use in this object
	 *	@param int $primarykey the field to use as primary key
	 *	@param int $id the value of the primary key that will be used to find the current row in the database
	 *  @param string $connection settings connection name to use for this instance.
	 */
	public function __setupDatabase($table, $fields, $primarykey, $id=false, $connection='Database')
	{
		$this->databaseInfo = new stdClass();
		$this->databaseInfo->table = $table;
		$this->databaseInfo->fields = $fields;
		$this->databaseInfo->primary = $primarykey;
		$this->databaseInfo->ID = $id;
		$this->databaseInfo->connection = $connection;
		$this->databaseValues = array();
		$this->changedValues = array();
		$this->relations = array();
		$this->orderProperty = false;
		$this->orderDirection = false;
		if($id) $this->__init();
	}

	/** 
	 *	Fills the current object with the corresponding row from the database. 
	 */
	private function __init() 
	{
		if($this->databaseInfo->ID != false) {
			$fieldnames = implode(",", array_keys($this->databaseInfo->fields));
			$input = dbConnection::getInstance($this->databaseInfo->connection)->fetchRow("select {$fieldnames} from {$this->databaseInfo->table} where {$this->databaseInfo->primary} = {$this->databaseInfo->ID}", 'assoc');
			$this->import($input);
		}
	}

	/** 
	 *	Catches the default getter and return the appropriate property
	 *  Checks if the current value is a mapped value, and if so, if it's a changed value or not (due to caching).
	 *	@param string $property the property being called.
	 */
	public function __get($property) { 
		$field = false;
		$field = $this->fieldForProperty($property);					  // are we calling the 'mapped' way?
		if(!$field) $field = (array_key_exists($property, $this->databaseInfo->fields)) ? $property : false;
		if($field != false && array_key_exists($field, $this->changedValues)) return($this->changedValues[$field]); // this is an updated property, return it.
		if($field != false && is_array($this->databaseValues) && array_key_exists($field, $this->databaseValues)) return($this->databaseValues[$field]); // hack to use non-defined
		if($field === false && is_array($this->databaseValues) && array_key_exists($property, $this->databaseValues)) return($this->databaseValues[$property]); // hack to use non-defined fields.
		if($field === false) {
			Logger::Trace("Field not found in ".get_class($this), $property);
			return false;
		}
	}


	/** 
	 *	Catches the default setter and handles the actions needed.
	 *  Checks if $property is a mapped property, and if so adds the new value to $this->changedValues.
	 *	@param string $property the property being called.
	 *  @param mixed $value the new value to be set.
	 */
	public function __set($property, $value) { // catch the default setter
		if($this->hasProperty($property)) {
			$this->changedValues[$this->fieldForProperty($property)] = $value;	
		}
		else
		{
			Logger::Trace("Tried to set a non-dbObject property for ".get_class($this), $property, $value);
		}	
	}	

	/**
	 * For serialization
	 */
	public function __sleep() 
	{
		$fields = array_keys(get_object_vars($this));
		return($fields);
	}

	/**
	 * Checks if a certain property is mapped to the database table
	 * @param string $property the property to check
	 * @returns boolean true if found, false if not.
	 */
	public function hasProperty($property) { 
		if (array_key_exists($property, $this->databaseInfo->fields) !== false) return true;
		if (array_search($property, $this->databaseInfo->fields) !== false) return true;
		if (array_search($property, $this->databaseValues) !== false ) return true;
		return false;
	}

	/**
	 * Finds a corresponding database fieldname for a property
	 * @param string $property the property to check
	 * @returns the corresponding property or false if none found
	 */
	public function fieldForProperty($property) { // get db field by it's property name
		if(array_key_exists($property, $this->databaseInfo->fields))	{
			return($property);
		}
		if(array_search($property, $this->databaseInfo->fields) !== false) {
			return(array_search($property, $this->databaseInfo->fields));
		}		
		return false;
	}
	
	/**
	 * Tells the database to delete the current mapped row
	 */
	public function DeleteYourself() { //deletes the current object from database.
		if($this->databaseInfo->ID !== false) {
			dbConnection::getInstance($this->databaseInfo->connection)->query("delete from {$this->databaseInfo->table} where {$this->databaseInfo->primary} = {$this->databaseInfo->ID}");
		}
	}

	/**
	 * dbObject::setOrderProperty()
	 * 
	 * Defines the default order to sort Find results by.
	 * 
	 * @param mixed $field field to sort
	 * @param string $order ASC / DESC
	 */
	public function setOrderProperty($field, $order='ASC') { 
		$this->orderProperty = $field;
		$this->orderDirection = $order;
	}

	/**
		Insert this object into the database:
		* prepare the query with just a null value for primary key
		* append the changed fields and (escaped)values of this object if needed
		* execute the query
	*/
	private function InsertNew()
	{
	        if($this->ID == false)
		{
			$insertfields = $this->databaseInfo->primary;
			$insertValues = 'null';
		}
		if (sizeof($this->changedValues) > 0) { // do we have any new-set values?
			$filteredValues = PostFilter::nl2mysql($this->changedValues);
			$insertfields .= ', '.implode(",", array_keys($filteredValues));
			foreach ($filteredValues as $property=>$value) { // append each value escaped to the query
				$insertValues .= ", '".dbConnection::getInstance($this->databaseInfo->connection)->escapeValue($value)."'";
				$this->databaseValues[$property] = $this->changedValues[$property]; // and store it so we don't save it again
			}
			$this->changedValues = array(); // then clear the changedValues 
		}
		
		$this->databaseInfo->ID = dbConnection::getInstance($this->databaseInfo->connection)->query("insert into {$this->databaseInfo->table} ({$insertfields}) values ($insertValues);");
		$this->databaseValues[$this->databaseInfo->primary] = $this->databaseInfo->ID; // update the primary key
		return($this->databaseInfo->ID); // and return it 
	}

	/**
	 * Updates the current row if $changedValues array is not empty.
	 * If $this->ID == false it will insert a new record.
	 * @returns int the newly inserted primary key or current id.
	 */
	public function Save() 
	{
		if(sizeof($this->changedValues) > 0 && $this->databaseInfo->ID == false) { // it's a new record for the db
			$id = $this->InsertNew();
			$this->analyzeRelations(); // re-analyze the relation types so we can use Find()
			if(array_search('onInsert', get_class_methods(get_class($this))) !== false) { $this->onInsert(); } // fire the onInsert event.			
			return $id;
		}
		elseif ($this->changedValues != false) { // otherwise just build the update query
			$updateQuery = "";
			$filteredValues = PostFilter::nl2mysql($this->changedValues);
			foreach ($filteredValues as $property=>$value) {
				$updateQuery .= ($updateQuery != '') ? ', ' : '';
				$updateQuery .= ($value != '') ? " {$property} = '".dbConnection::getInstance($this->databaseInfo->connection)->escapeValue($value)."'" : "{$property} = NULL";
				$this->databaseValues[$property] = $this->changedValues[$property]; // store the value so we don't have to save it again
			}
			dbConnection::getInstance($this->databaseInfo->connection)->query("update {$this->databaseInfo->table} set {$updateQuery} where {$this->databaseInfo->primary} = {$this->databaseInfo->ID}");
			$this->changedValues = array(); 
			return($this->databaseInfo->ID);
		}	
		return false;
	}
		
	/**
	 * Add a new relation to the relation list and set it to be analyzed if used.
	 * Newly added relations will standard have RELATION_NOT_ANALYZED for relationtype to optimize speed
	 * @param string $classname Connecting classname
	 * @param string $connectorclassname Classname to use as connector class
 	 */
	public function addRelation($classname, $connectorclassname=false) 
	{
		$this->relations[$classname] = new stdClass();
		$this->relations[$classname]->relationType = RELATION_NOT_ANALYZED;
		if($connectorclassname != false) $this->relations[$classname]->connectorClass = $connectorclassname;
		if($this->databaseInfo->ID != false) $this->analyzeRelations();		
	}


	/**
	 * New function to add the custom relation mappings. Now you no longer need matching primary keys to have a connection.
	 * E.G Map Customers.ID to Contracts.Customer_ID 
	 *
	 * Usage: $this->addCustomRelation($targetclass, $sourceclassproperty, $targetclassProperty)
	 * Do not forget to do this in both classes. For a relation between a Customers and a Contracts object as shown above, you need to do the following:
	 * //class customer -> __construct()
	 * $this->addCustomRelation('Contract', 'ID', 'Customer_ID');
	 * // class contract -> __construct()
	 * $this->addCustomRelation('Customer', 'Customer_ID', 'ID');
	 * All Find() connect and disconnect functions work transparently with this new method. 
	 */
	/**
	 * dbObject::addCustomRelation()
	 * 
	 * @param mixed $classname
	 * @param mixed $sourceproperty
	 * @param mixed $targetproperty
	 * @return
	 */
	function addCustomRelation($classname, $sourceproperty, $targetproperty)
	{
		if(!$this->hasProperty($sourceproperty)) {
			Logger::Trace("Error in addCustomRelation: ".get_class($this)." hasn't got property ".$sourceproperty.", so couldn't connect to ".$classname);	
		}
		else
		{
			$this->relations[$classname] = new stdClass();
			$this->relations[$classname]->relationType = RELATION_CUSTOM;					
			$this->relations[$classname]->sourceProperty = $sourceproperty;
			$this->relations[$classname]->targetProperty = $targetproperty;
		}
	}


	/** 
	 * This is where the true magic happens. It will analyze what kind of DB relation we're using. (1:1, 1:many, many:many)
	*/
	public function analyzeRelations() 
	{
		foreach($this->relations as $classname=>$info) {
			if(is_subclass_of($classname, 'dbObject')) {// the class to connect is a dbObject
				$obj = new $classname(false);
				$info->className = $classname;
				if($info->relationType == RELATION_NOT_ANALYZED)
				{
					if(array_key_exists('connectorClass', get_object_vars($info)) && $info->connectorClass != '' && is_subclass_of($info->connectorClass, 'dbObject')) { // this class has a connector class. It should be a many:many relation
						$connector = $info->connectorClass;
						$connectorobj = new $connector(false);
						if(array_key_exists($this->databaseInfo->primary, $connectorobj->databaseInfo->fields) && array_key_exists($obj->databaseInfo->primary, $connectorobj->databaseInfo->fields)) {
							$info->relationType = RELATION_MANY; // yes! The primary key of the relation now appears in this object, the connector class and one of the connected class. it's a many:many relation
							continue;
						} 
						else { 
							unset($info->connectorClass); // it's not connected to our relations
						}
					}
					if(	array_key_exists($obj->databaseInfo->primary, $this->databaseInfo->fields) && array_key_exists($this->databaseInfo->primary, $obj->databaseInfo->fields)) {
						$info->relationType = RELATION_SINGLE; // if the primary key of the connected object exists in this object and the primary key of this object exists in the connected object it's a 1:1 relation
					}
					elseif((array_key_exists($this->databaseInfo->primary, $obj->databaseInfo->fields) && !array_key_exists($obj->databaseInfo->primary, $this->databaseInfo->fields) || !array_key_exists($this->databaseInfo->primary, $obj->databaseInfo->fields) && array_key_exists($obj->databaseInfo->primary, $this->databaseInfo->fields)) ) {
							$info->relationType = RELATION_FOREIGN;	// if the primary key of the connected object exists in this object (or the other way around), but the primary key of this object does not exist in the connected object (or the other way around) it's a many:1 or 1:many relation		
					}
					elseif($info->relationType == RELATION_NOT_ANALYZED) {
						$info->relationType = RELATION_NOT_RECOGNIZED;  // we don't recognize this type of relation.
						Logger::Trace("Warning! Relation not recognized! {$classname} connecting to ".get_class($this)); 
					}
					$this->relations[$classname] = $info;
				}
			}
			else
			{
				Logger::Trace ("{$classname} is not a dbObject!");
				unset($this->relations[$classname]); // tried to connect a non-dbobject object.
			}
		}	
	}

	
	/*
		This connects 2 dbObjects together, with a connector class if needed.
	 * Runs relation analyzer if needed.
	 * @uses analyzeRelations
	 * @param object $object the class to connect.
	*/
	/**
	 * dbObject::Connect()
	 * 
	 * @param mixed $object
	 * @return
	 */
	public function Connect($object) 
	{
		$className = get_class($object);
		if($this->databaseInfo->ID == false) $this->Save(); // save both objects if they are new
		if($object instanceof dbObject && $object->databaseInfo->ID == false) $object->Save(); 	
		if(array_key_exists($className, $this->relations)) {
			switch($this->relations[$className]->relationType)
			{
				case RELATION_NOT_ANALYZED:
					$this->analyzeRelations(); // if we didn't run the analyzer yet, run it.
					$this->Connect($object); // run connect function again.
				break;
				case RELATION_SINGLE: // link the 2 objects' primary keys
					$this->changedValues[$object->databaseInfo->primary] = $object->databaseInfo->ID;
					$object->changedValues[$this->databaseInfo->primary] = $this->databaseInfo->ID;	 
				break;
				case RELATION_FOREIGN: // determine wich one needs to have the primary key set for the 1:many or many:one relation 
					if(array_key_exists($this->databaseInfo->primary, $object->databaseInfo->fields)) {
						$object->changedValues[$this->databaseInfo->primary] = $this->databaseInfo->ID;
					}
					elseif(array_key_exists($object->databaseInfo->primary, $this->databaseInfo->fields)) {
						$this->changedValues[$object->databaseInfo->primary] = $object->databaseInfo->ID;
					}
				break;
				case RELATION_MANY: // create a new connector class, set both primary keys and save it.
					$connector = $this->relations[$className]->connectorClass;
					$connector = new $connector(false);
					$property = $connector->databaseInfo->fields[$this->databaseInfo->primary];
					$connector->$property = $this->databaseInfo->ID;
					$property = $connector->databaseInfo->fields[$object->databaseInfo->primary];
					$connector->$property = $object->databaseInfo->ID;
					$connector->Save();
				break;
				case RELATION_CUSTOM:  // determine wich one needs to have the primary key set for the 1:many or many:one relation 
					if($this->fieldForProperty($this->relations[$className]->sourceProperty) != $this->databaseInfo->primary) { // we don't want to change primary keys. This is a good way to check which value to change
						$targetval = $this->relations[$className]->targetProperty;
						$this->changedValues[$this->fieldForProperty($this->relations[$className]->sourceProperty)] = $object->$targetval;
					}
					else {
						$targetval = $this->relations[$className]->sourceProperty;
						$object->changedValues[$this->relations[$className]->targetProperty] = $this->ID;	
					}
				break;
			}
			$this->Save(); // save both objects to store changed values.
			$object->Save();
		}	
	}

	/**
	 * Disconnects the relation between 2 objects.
	 * Runs relation analyzer if needed.
	 * @uses analyzeRelations
	 * @param object $object the class to disconnect.
	 */
	public function Disconnect($object, $id=false) 
	{
		if(!$object && !$id) return;
		if(!$object instanceof dbObject && $id != false) {
			$object = new $object(false);
			$object->databaseInfo->ID = $id; // a tweak to disconnect an uninitialized object so that it doesn't have to fetch the whole row first.
		}
		$className = get_class($object);
		if(array_key_exists($className, $this->relations)) {
			switch($this->relations[$className]->relationType)
			{
				case RELATION_SINGLE:
					$this->changedValues[$object->databaseInfo->primary] = '';
					$object->changedValues[$this->databaseInfo->primary] = '';
				break;
				case RELATION_FOREIGN:
					if(array_key_exists($this->databaseInfo->primary, $object->databaseInfo->fields)) {
						$object->changedValues[$this->databaseInfo->primary] = '';
					}
					elseif(array_key_exists($object->databaseInfo->primary, $this->databaseInfo->fields)) {
						$this->changedValues[$object->databaseInfo->primary] = '';
					}
				break;
				case RELATION_MANY:
					$input = dbObject::search($this->relations[$className]->connectorClass, array($object->databaseInfo->primary => $object->databaseInfo->ID, $this->databaseInfo->primary => $this->databaseInfo->ID)); // search for a connector with both primaries
					if($input) $input[0]->deleteYourSelf();
				break;
				case RELATION_CUSTOM:  // determine wich one needs to have the primary key set for the 1:many or many:one relation 
					if($this->fieldForProperty($this->relations[$className]->sourceProperty) != $this->databaseInfo->primary) { // we don't want to change primary keys. This is a good way to check which value to change
						$targetval = $this->relations[$className]->targetProperty;
						$this->changedValues[$this->fieldForProperty($this->relations[$className]->sourceProperty)] = '';
					}
					else {
						$targetval = $this->relations[$className]->sourceProperty;
						$object->changedValues[$this->relations[$className]->targetProperty] = '';	
					}
				break;
			}
			$this->Save();
			$object->Save();
		}	
	}

	/**
	 * Checks if this is a 'connecting' object between 2 tables by checking if the passed classname is a connection class.
	 * @param string $className Classname to check
	 * @returns boolean
	 */
	private function isConnector($className)
	{
		foreach ($this->relations as $key => $val) { // walk all relations
			if(array_key_exists('connectorClass', get_object_vars($val)) && $val->connectorClass == $className) return true; 
		}
		return false;	
	}

	/**
	 * Imports a pre-filled object (like a table row) into this object
	 * @param array $values Database values to fill this object with
	 */
	public function Import($values) { 
		$this->databaseValues = PostFilter::mysql2nl($values);
		$this->databaseInfo->ID = (!empty($values[$this->databaseInfo->primary])) ? $values[$this->databaseInfo->primary] : false;
	}
	
	/**
	 * Imports a pre-filled settings array to the object.
	 * @param array $values Settings keys/values to fill this object with
	 */
	public function ImportDefaults($array)
	{
		foreach($array as $key=>$val) 
		{
			$this->$key = $val; 
		}
	}


	/**
		Imports an array of e.g. db rows and returns filled instances of $className
		This will not run the analyzerelations or other stuff for performance and recursivity reasons.
	 *  @param string $className ClassName to cast to
	 *  @param array $input recursive array of records to import.
	 */
	public static function importArray($className, $input) 
	{
		$output = array();
		if($input != false && sizeof($input) > 0)
		{
			foreach ($input as $array) 
			{
				$elm = new $className(false);
				$elm->Import($array);
				if($elm->ID != false) $output[] = $elm;
			}
		}
		return(sizeof($output) > 0 ? $output : false);	
	}

	/**
	  * Is the passed class a relation of $this? 
	  * @param $class classname to test
	  * @returns boolean isRelation
	  */
	private function isRelation($class) 
	{
		if (strtolower($class) == strtolower(get_class($this))) { return(get_class($this)); }
		if(!empty($this->relations)){
			foreach($this->relations as $key=>$val) if(strtolower($class) == strtolower($key)) return($key);
		}
		Logger::Trace("Error in isRelation! {$class} is not a relation of ".get_class($this));
		return false;
	}

	/**
	The awesome find function. Creates a QueryBuilder Object wich creates a Query to find all objects for your filters.
	 * <code>
	 * //  Syntax for the filters array: 
	 * Array(
 	 *		'ID > 500', // just a key element, it will detect this, map the fields and just execute it.
	 *		'property'=> 'value' // $property of $classname has to be $value 
	 *		Array('ClassName'=> array('property'=>'value')// Filter by a (relational) class's property. You can use this recursively!!
	 * ) 
	 * </code>
 	 * @param string $className Classname to find (has to be a relation of $this or get_class($this))
	 * @param array $filters array of filters to use in query
	 * @param array $extra array of eventual order by / group by parameters
	 * @param array $justThese Fetch only these fields from the table. Useful if you don't want to fetch large text or blob columns.
	 * @uses QueryBuilder to build the actual query
	 * @returns array a batch of pre-filled objects of $className or false if it finds nothing
	 */
	public function Find($className, $filters=array(), $extra=array(), $justThese=array()) 
	{
		$originalClassName = ($className instanceof dbObject) ? get_class($className) : $className;
		$class = new $originalClassName();
		if($originalClassName != get_class($this) && $this->ID != false) {
			$filters["ID"] = $this->ID;
			$filters = array(get_class($this) => $filters);	
		}
		$builder = new QueryBuilder($originalClassName, $filters, $extra, $justThese);
		$input = dbConnection::getInstance($this->databaseInfo->connection)->fetchAll($builder->buildQuery(), 'assoc');
		return(dbObject::importArray($originalClassName, $input));
	}
	
	/**
	 * dbObject::findCount()
	 * Finds the number of results that would be fetched for this query.
	 * 
	 * @param string $className Classname to find (has to be a relation of $this or get_class($this))
	 * @param array $filters array of filters to use in query
	 * @param array $extra array of eventual order by / group by parameters
	 * @param array $justThese Fetch only these fields from the table. Useful if you don't want to fetch large text or blob columns.
	 * @uses QueryBuilder to build the actual query
	 * @return int number of results;
	 */
	function findCount($className, $filters, $extra=array(), $justThese= array())
	{
		$originalClassName = ($className instanceof dbObject) ? get_class($className) : $className;
		$class = new $originalClassName();
		if($originalClassName != get_class($this) && $this->ID != false) {
			$filters["ID"] = $this->ID;
			$filters = array(get_class($this) => $filters);	
		}
		$builder = new QueryBuilder($originalClassName, $filters, $extra, $justThese);
		return ( $builder->getCount());
	}
	
	/**
	 * Static wrapper around the Find function function. 
	 * @see Find for how this works.
 	 * @param string $className Classname to find (has to be a relation of $this or get_class($this))
	 * @param array $filters array of filters to use in query
	 * @param array $extra array of eventual order by / group by parameters
	 * @param array $justThese Fetch only these fields from the table. Useful if you don't want to fetch large text or blob columns.
	 * @uses Find to build the actual query
	 * @returns array a batch of pre-filled objects of $className or false if it finds nothing
	 */
	static function Search($className, $filters=array(), $extra=array(), $justThese=array())
	{
		$class = new $className();
		if($class instanceOf dbObject)
		{
			return($class->Find($className, $filters, $extra, $justThese));
		}
	}

	static function SearchCount($className, $filters =array(), $extra = array(), $justTheses = array())
	{
		$class = new $className;
		if($class instanceOf dbObject)
		{
			return($class->FindCount($className, $filters, $extra, $justThese));
		}
	}

	/**
	 * Destructor auto-calls $this->Save().
	 * @uses Save
	 */
	public function __destruct()
	{
		$this->Save(); // try to save the object if changed.
	}
}

/**
 * The helper class that analyzes what joins to use in the select queries 
 * @package Libraries
 */
/**
 * QueryBuilder
 * 
 * @package Pork
 * @author Jelle Ursem
 * @copyright Belfabriek 2009
 * @version 1.0
 * @access public
 */
class QueryBuilder 
{
	var $class, $fields, $filters, $extras, $justthese, $joins, $groups, $wheres, $limit, $orders;

	/**
	 * QueryBuilder::__construct()
	 * 
	 * @param mixed $class
	 * @param mixed $filters
	 * @param mixed $extras
	 * @param mixed $justthese
	 * @return
	 */
	public function __construct($class, $filters=array(), $extras=array(), $justthese=array())
	{
		$this->class= $class;
		$this->filters = $filters;
		$this->extras = $extras;
		$this->wheres = array();
		$this->joins = array();
		$this->fields = array();
		$this->orders = array();
		$this->groups = array();
		if(!($this->class instanceof dbObject)) $this->class = new $class();
		$tableName = $this->class->databaseInfo->table;
		if(sizeof($justthese) == 0) { // if $justthese is not passed, use all fields from $class->databaseInfo->fields
			$fields = array_keys($this->class->databaseInfo->fields);
			foreach($fields as $property) $this->fields[] = $tableName.'.'.$this->class->fieldForProperty($property);
		}
		else { // otherwise, use only the fields from $justthese
			foreach($justthese as $property) $this->fields[] = $tableName.'.'.$this->class->fieldForProperty($property);
		}
		if(sizeof($filters) > 0 )
		{
			foreach($filters as $property=>$value) $this->buildFilters($property, $value, $this->class);
		}
		$this->buildOrderBy();
		
	}

	/**
	 * QueryBuilder::buildFilters()
	 * This is the tricky part. You can mix both sql wheres as key/values and you can also use a dbObject class as an array key, then it will auto-join that table.
	 * Syntax then works like this:
	 * 
	 * <pre>
	 * $input = dbObject::Search('SkillGroupFlowRelation', 
	 *			Array('FlowRouting' =>
	 *				Array("MainTimeframeRelation" => 
	 *					Array("MainRouting"=> 
	 *						Array("SrnMainRelation" => 
	 *							Array("Srn" => Array("ID"=>$this->srn->ID)))))));
	 * </pre>
	 * This finds a SkillGroupFlowRelation connected to a FlowRouting, which is chained down until an Srn Object with id $this->srn->ID.
	 * It automatically generates this query:
	 *
	 * <pre>
	 *	SELECT skillgroup_flow_relation.sf_id, 
	 *		skillgroup_flow_relation.sf_modified, 
	 *		skillgroup_flow_relation.sf_created, 
	 *		skillgroup_flow_relation.sf_flow_id, 
	 *		skillgroup_flow_relation.sf_queue_id, 
	 *		skillgroup_flow_relation.sf_order_pos, 
	 *		skillgroup_flow_relation.sf_description, 
	 *		skillgroup_flow_relation.sf_max_tries, 
	 *		skillgroup_flow_relation.sf_max_ringtime, 
	 *		skillgroup_flow_relation.sf_prompt_wait_start, 
	 *		skillgroup_flow_relation.sf_prompt_wait_between, 
	 *		skillgroup_flow_relation.sf_prompt_silence, 
	 *		skillgroup_flow_relation.sf_target_type, 
	 *		skillgroup_flow_relation.sf_target_id
	 *	 FROM 
	 *		skillgroup_flow_relation
	 *	 LEFT JOIN 
	 *		 flow_routing on skillgroup_flow_relation.sf_flow_id = flow_routing.fr_id
	 *	 LEFT JOIN 
	 *		 main_timeframe_relation on flow_routing.fr_id = main_timeframe_relation.mtr_flow_id
	 *	 LEFT JOIN 
	 *		 main_routing on main_timeframe_relation.mtr_mr_id = main_routing.mr_id
	 *	 LEFT JOIN 
	 *		 srn_main_relation on main_routing.mr_id = srn_main_relation.smr_mr_id
	 *	 LEFT JOIN 
	 *		 srn on srn_main_relation.smr_srn_id = srn.id WHERE srn.id = '134' 
	 *
	 * </pre>
	 *
	 * @param mixed $what what to find: a class or a field in an $class
	 * @param string $value the value that the searchfield needs to have
	 * @param mixed $class the class to find the property in
	 */
	private function buildFilters($what, $value, $class)
	{
		
		$wtclass = (array_key_exists($what, $class->relations))  ? new $what() :false;

		if($wtclass instanceof dbObject && is_array($value)) {  // filter by a property of a subclass
			foreach($value as $key=>$val) {
				$this->buildFilters($key, $val, $wtclass);
				$this->buildJoins($wtclass,$class);
			}	
		}
		elseif(is_numeric($what)) { // it's a custom whereclause (not just $field=>$value)		
			if((!$class instanceof dbObject)) $class = new $class();
			$value = dbConnection::getInstance($this->class->databaseInfo->connection)->escapeValue($value);
			$this->wheres[] = $this->mapFields($value, $class);
		}
		else { // standard $field=>$value whereclause. Prefix with tablename for speed.

			if((!$class instanceof dbObject)) $class = new $class();
			$value = dbConnection::getInstance($this->class->databaseInfo->connection)->escapeValue($value);

			$what = $class->fieldForProperty($what);
			$this->wheres[] = "{$class->databaseInfo->table}.{$what} = '{$value}'";
		}
	}

	/**
	 * QueryBuilder::buildOrderBy()
	 * 
	 * @return
	 */
	private function buildOrderBy()	// filter the 'extras' paramter for order by, group by and limit clauses.
	{
		$hasorderby = false;
		foreach($this->extras as $key=>$extra) {
			if(strpos(strtoupper($extra), 'ORDER BY') !== false) {
				$this->orders[] = $this->mapFields(str_replace('ORDER BY', "", strtoupper($extra)), $this->class);
				unset($this->extras[$key]);
			}
			if(strpos(strtoupper($extra), 'LIMIT') !== false) {
				unset($this->extras[$key]);
				$this->limit = $this->mapFields($extra, $this->class);
			}
			if(strpos(strtoupper($extra), 'GROUP BY') !== false) { 
				$this->groups[] = $this->mapFields(str_replace('GROUP BY', "", strtoupper($extra)), $this->class);
				unset($this->extras[$key]);
			}
		}
		if($this->class->orderProperty && $this->class->orderDirection && sizeof($this->orders) == 0) {
			$this->orders[] = $this->mapFields("{$this->class->orderProperty} ", $this->class).$this->class->orderDirection;
		}
	}

	
	/**
	 * QueryBuilder::mapFields()
	 * 
	 * @param mixed $query
	 * @param mixed $object
	 * @return
	 */
	private function mapFields($query, $object) // map the 'pretty' fieldnames to db table fieldnames.
	{
		$reserved = Array('LIMIT', 'ORDER', 'BY', 'GROUP','DESC','ASC','');
		$words = preg_split("/([\s|\W]+)/", $query, -1, PREG_SPLIT_DELIM_CAPTURE);	
		if(!empty($words)) {
			foreach($words as $key=>$val) { 
				if(strlen(trim($val)) < 2) continue;
				if(array_search(trim(strtoupper($val)), $reserved) !== false) continue;
				if(is_numeric($val)) continue;
				if(strpos($val, '.') !== false) {
					$expl = explode(".", $val);
					if(sizeof($expl) == 2 && $expl[0] == $object->databaseInfo->table)  $val = $expl[1];
					else continue;
				}
				if($object->hasProperty($val)) { 
					$words[$key] = $object->databaseInfo->table.'.'.$object->fieldForProperty($val);
				}
			} 
		}
		return(implode("", $words));
	}

	/**
	 * QueryBuilder::buildJoins()
	 * 
	 * @param mixed $class
	 * @param bool $parent
	 * @return
	 */
	private function buildJoins($class, $parent=false) // determine what joins to use
	{
		if(!$parent) return;	// first do some checks for if we have uninitialized classnames
		if(!($class instanceof dbObject)) $class = new $class(); 
		$className = get_class($class);
		if(!($parent instanceof dbObject)) $parent = new $parent();
		switch($parent->relations[$className]->relationType) { // then check the relationtype
			case RELATION_NOT_ANALYZED:							// if its not analyzed, it's new. Save + analyze + re-call this function.
				if(sizeof($class->changedValues) > 0) $class->Save();
				$parent->analyzeRelations();
				return($this->buildJoins($class, $parent));
			break;
			case RELATION_SINGLE:
			case RELATION_FOREIGN:								// it's a foreign relation. Join the appropriate table.
				if($class->hasProperty($parent->databaseInfo->primary)) 
				{
					$this->joins[] = "LEFT JOIN \n\t {$class->databaseInfo->table} on {$parent->databaseInfo->table}.{$parent->databaseInfo->primary} = {$class->databaseInfo->table}.{$parent->databaseInfo->primary}";
				}
				else if($parent->hasProperty($class->databaseInfo->primary)) 
				{
					$this->joins[] = "LEFT JOIN \n\t {$class->databaseInfo->table} on {$class->databaseInfo->table}.{$class->databaseInfo->primary} = {$parent->databaseInfo->table}.{$class->databaseInfo->primary}";
				}
			break;
			case RELATION_MANY:									// it's a many:many relation. Join the connector table and then the other one.
				$connectorClass = $parent->relations[$className]->connectorClass;
				$conn = new $connectorClass(false);
				$this->joins[] = "LEFT JOIN \n\t {$conn->databaseInfo->table} on  {$conn->databaseInfo->table}.{$parent->databaseInfo->primary} = {$parent->databaseInfo->table}.{$parent->databaseInfo->primary}";
				$this->joins[] = "LEFT JOIN \n\t {$class->databaseInfo->table} on {$conn->databaseInfo->table}.{$class->databaseInfo->primary} = {$class->databaseInfo->table}.{$class->databaseInfo->primary}";
			break;
			case RELATION_CUSTOM:
				$this->joins = array_merge(array("LEFT JOIN \n\t {$class->databaseInfo->table} on {$parent->databaseInfo->table}.{$parent->relations[$className]->sourceProperty} = {$class->databaseInfo->table}.{$parent->relations[$className]->targetProperty}"), $this->joins);
				
					$this->joins[] = "LEFT JOIN \n\t {$class->databaseInfo->table} on {$parent->databaseInfo->table}.{$parent->relations[$className]->sourceProperty} = {$class->databaseInfo->table}.{$parent->relations[$className]->targetProperty}";
			break;
			default:
				Logger::Trace("Warning! class ".get_class($parent)." probably has no relation defined for class {$className}  or you did something terribly wrong...", $parent->relations[$className]);

			break;
		}		
		$this->joins = array_unique($this->joins);
	}
	
	/**
	 * QueryBuilder::buildQuery()
	 * 
	 * @return
	 */
	public function buildQuery() // joins all the previous stuff together.
	{
		$where = (sizeof($this->wheres) > 0) ? ' WHERE '.implode(" \n AND \n\t", $this->wheres) : '';
		$order = (sizeof($this->orders) > 0) ? ' ORDER BY '.implode(", ", $this->orders) : '' ;
		$group = (sizeof($this->groups) > 0) ? ' GROUP BY '.implode(", ", $this->groups) : '' ;
		$query = 'SELECT '.implode(", \n\t", $this->fields)."\n FROM \n\t".$this->class->databaseInfo->table."\n ".implode("\n ", $this->joins).$where.' '.$group.' '.$order.' '.$this->limit;
		return($query);
	}

	/**
	 * QueryBuilder::getCount()
	 * 
	 * @return
	 */
	function getCount()
	{
		$where = (sizeof($this->wheres) > 0) ? ' WHERE '.implode(" \n AND \n\t", $this->wheres) : '';
		$order = (sizeof($this->orders) > 0) ? ' ORDER BY '.implode(", ", $this->orders) : '' ;
		$group = (sizeof($this->groups) > 0) ? ' GROUP BY '.implode(", ", $this->groups) : '' ;
		$query = "SELECT count(*) FROM \n\t".$this->class->databaseInfo->table."\n ".implode("\n ", $this->joins).$where.' '.$group.' '.$order.' ';

		return dbConnection::getInstance($this->class->databaseInfo->connection)->fetchOne($query);

	}
}

 