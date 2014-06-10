<?php

class ActiveRecord
{
    // configurables
    /**
     * Name of table
     * @var string
     */
    static public $tableName = 'records';
    
    /**
     * Noun to describe singular object
     * @var string
     */
    static public $singularNoun = 'record';
    
    /**
     * Noun to describe a plurality of objects
     * @var string
     */
    static public $pluralNoun = 'records';
    
    /**
     * String to identify this class with in administrative interfaces
     * @var string
     */
    static public $classTitle = 'Untitled Class';
    
    /**
	 * URL that can be prefixed to this record's identifier by $this->getURL
     * to generate a domain-relative address to this record
	 * @var string
	 */
    static public $collectionRoute;
	
	/**
	 * Defaults values for field definitions
	 * @var array
	 */
	static public $fieldDefaults = array(
		'type' => 'string'
		,'notnull' => true
	);
	
	/**
	 * Field definitions
	 * @var array
	 */
	static public $fields = array(
		'ID' => array(
			'type' => 'integer'
			,'autoincrement' => true
			,'unsigned' => true
            ,'includeInSummary' => true
		)
		,'Class' => array(
			'type' => 'enum'
			,'notnull' => true
			,'values' => array()
		)
    	,'Created' => array(
			'type' => 'timestamp'
			,'default' => 'CURRENT_TIMESTAMP'
		)
		,'CreatorID' => array(
			'type' => 'integer'
			,'notnull' => false
		)
	);
	
	/**
	 * Index definitions
	 * @var array
	 */
	static public $indexes = array();
    
    /*
	* Validation checks
	* @var array
	*/
	static public $validators = array();
	
	/**
	 * Relationship definitions
	 * @var array
	 */
	static public $relationships = array(
		'Creator' => array(
			'type' => 'one-one'
			,'class' => 'Person'
			,'local' => 'CreatorID'
		)
	);
    
	/*
	* Dynamic field definitions
	* @var array
	*/
	static public $dynamicFields = array(
        'validationErrors' => array(
            'method' => 'getValidationErrors'
        )
    );
    
	/*
	* Search Condition definitions
	* @var array
	*/
	static public $searchConditions = array();

	/*
	* Virtual fields for sorting
	* @var array
	*/
	static public $sorters = array();

	/**
	 * True to cache model instances in shared memory
	 */
	static public $useCache = false;
    
	/**
	 * True to track modification time/person
	 */
	static public $trackModified = false;
	
	// support subclassing
	static public $rootClass = null;
	static public $defaultClass = null;
	static public $subClasses = null;

	// protected members
    protected static $_stackedConfigs = array();
	
	protected $_record;
	protected $_convertedValues;
	protected $_relatedObjects = array();
	protected $_isDirty;
	protected $_isPhantom;
	protected $_isValid = true;
	protected $_isNew = false;
	protected $_isUpdated = false;
    protected $_isDestroyed = false;
	protected $_validator;
	protected $_validationErrors = array();
	protected $_originalValues = array();
	
	
	// magic methods
	/**
	 * Emergence extended magic method called after class and configuration are loaded
	 */
	static function __classLoaded()
	{
        // TODO: apply these field changes to the cached stacked field config, as writing to static::$fields can be unpredictable
        if (static::$trackModified) {
            if (empty(static::$fields['Modified'])) {
                static::$fields['Modified'] = array(
                    'type' => 'timestamp'
                    ,'notnull' => false
                );
            }
            if (empty(static::$fields['ModifierID'])) {
                static::$fields['ModifierID'] = array(
                    'type' => 'uint'
                    ,'notnull' => false
                );
            }
        }
	}
	
	public function __construct($record = array(), $isDirty = false, $isPhantom = null)
	{
		$this->_record = static::_convertRecord($record);
		$this->_isPhantom = isset($isPhantom) ? $isPhantom : empty($record);
		$this->_isDirty = $this->_isPhantom || $isDirty;

		// authorize read access
		if(!$this->userCanReadRecord())
		{
			throw new UserUnauthorizedException('Read authorization denied');
		}
		
		// set Class
		if(static::_fieldExists('Class') && !$this->Class)
		{
			$this->Class = get_class($this);
		}
		
	}

    public function __toString()
    {
        if ($this->isPhantom) {
            return $this->Class . ' [phantom]';
        } elseif ($url = $this->getURL()) {
            return $url;
        } else {
            return $this->Class . ' #' . $this->ID;
        }
    }
    
    static protected function _initStackedConfig($propertyName)
    {
    	$className = get_called_class();
		
		// merge fields from first ancestor up
		$classes = class_parents($className);
		array_unshift($classes, $className);
		
		$config = array();
		while ($class = array_pop($classes)) {
            $classVars = get_class_vars($class);
			if (!empty($classVars[$propertyName])) {
				$config = array_merge($config, $classVars[$propertyName]);
			}
		}
        
        // filter out falsey configs
        $config = array_filter($config);
        
        // apply property-specific initialization
        if ($propertyName == 'fields') {
            $config = static::_initFields($config);
        } elseif ($propertyName == 'relationships') {
            $config = static::_initRelationships($config);
        } elseif ($propertyName == 'dynamicFields') {
            $config = static::_initDynamicFields($config);
        } elseif ($propertyName == 'validators') {
            $config = static::_initValidators($config);
        }
        
        return $config;
    }
    
    static public function &getStackedConfig($propertyName, $key = null)
    {
        $className = get_called_class();

        if (!isset(static::$_stackedConfigs[$className][$propertyName])) {
            static::$_stackedConfigs[$className][$propertyName] = static::_initStackedConfig($propertyName);
        }
        
        if ($key) {
            return static::$_stackedConfigs[$className][$propertyName][$key];
        } else {
            return static::$_stackedConfigs[$className][$propertyName];
        }
    }
    
    static public function aggregateStackedConfig($propertyName)
    {
        $config = array();

        foreach (static::getStaticSubClasses() AS $subClass) {
            $config = array_merge($config, $subClass::getStackedConfig($propertyName));
        }

        return $config;
    }
	
	static protected function _convertRecord($record)
	{	
		return $record;
	}
	
	function __get($name)
	{
		return $this->getValue($name);
	}
	
	function __set($name, $value)
	{
		return $this->setValue($name, $value);
	}
	
	function __isset($name)
	{
		return $this->getValue($name) !== null;
	}
	
	public function getValue($name)
	{
		switch($name)
		{
			case 'isDirty':
				return $this->_isDirty;
				
			case 'isPhantom':
				return $this->_isPhantom;
				
			case 'isValid':
				return $this->_isValid;
				
			case 'isNew':
				return $this->_isNew;
    			
			case 'isUpdated':
				return $this->_isUpdated;
    			
			case 'isDestroyed':
				return $this->_isDestroyed;
				
			case 'validationErrors':
				return $this->getValidationErrors();

			case 'originalValues':
				return $this->_originalValues;
				
			default:
			{
				// handle field
				if(static::_fieldExists($name))
				{
					return $this->_getFieldValue($name);
				}
				// handle relationship
				elseif(static::_relationshipExists($name))
				{
					return $this->_getRelationshipValue($name);
				}
				// default Handle to ID if not caught by fieldExists
				elseif($name == 'Handle')
				{
					return $this->ID;
				}
				// handle a dot-path to related record field
				elseif(count($path = explode('.', $name)) >= 2 && static::_relationshipExists($path[0]))
				{
					$related = $this->_getRelationshipValue(array_shift($path));

					while(is_array($related))
					{
						$related = $related[array_shift($path)];
					}

					return is_object($related) ? $related->getValue(implode('.',$path)) : $related;
				}
				// undefined
				else
				{
					return null;
				}
			}
		}
	}
	
	public function setValue($name, $value)
	{
		// handle field
		if(static::_fieldExists($name))
		{
			$this->_setFieldValue($name, $value);
		}
		// handle relationship
		elseif(static::_relationshipExists($name))
		{
			$this->_setRelationshipValue($name, $value);
		}
		// undefined
		else
		{
			return false;
		}
	}
	
	
	// public methods
    public function getTitle()
    {
        return static::fieldExists('Title') ? $this->Title : "$this->Class #$this->ID";
    }

    public function getHandle()
    {
        return static::fieldExists('Handle') ? $this->Handle : $this->ID;
    }

    public function getURL($suffix = '/', $params = array())
    {
        $url = static::$collectionRoute;
        
        if (!$url) {
            return null;
        }
        
        $url .= '/' . $this->getHandle();

        $suffix = ltrim($suffix, '/');
        
        if ($suffix) {
            $url .= '/' . $suffix;
        }
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }

	public function userCanReadRecord()
	{
		return true;
	}
    
    public function userCanEnumerateField($field)
    {
        $fieldOptions = static::getStackedConfig('fields', $field);
        
        if (!empty($fieldOptions['accountLevelEnumerate'])) {
            return empty($GLOBALS['Session']) ? false : $GLOBALS['Session']->hasAccountLevel($fieldOptions['accountLevelEnumerate']);
        }
        
        return true;
    }
    
    public function userCanEnumerateDynamicField($field)
    {
        $fieldOptions = static::getStackedConfig('dynamicFields', $field);
        
        if (!empty($fieldOptions['accountLevelEnumerate'])) {
            return empty($GLOBALS['Session']) ? false : $GLOBALS['Session']->hasAccountLevel($fieldOptions['accountLevelEnumerate']);
        }
        
        return true;
    }
    
    public function userCanWriteField($field)
    {
        $fieldOptions = static::getFieldOptions($field);
        
        if (!empty($fieldOptions['accountLevelWrite'])) {
            return empty($GLOBALS['Session']) ? false : $GLOBALS['Session']->hasAccountLevel($fieldOptions['accountLevelWrite']);
        }
        
        return true;
    }
	
	static public function create($values = array(), $save = false)
	{
		$className = get_called_class();
		
		// create class
		$ActiveRecord = new $className();
		$ActiveRecord->setFields($values);
		
		if($save)
		{
			$ActiveRecord->save();
		}
		
		return $ActiveRecord;
	}
	
	public function clearRelatedObject($name)
	{
		if(!$name)
		{
			return false;
		}

		unset($this->_relatedObjects[$name]);
	}
	
	public function isA($class)
	{
		return is_a($this, $class);
	}
	
	
	public function addValidationErrors($array)
	{
		foreach($array AS $field => $errorMessage)
		{
			$this->addValidationError($field, $errorMessage);
		}
	}

	public function addValidationError($field, $errorMessage)
	{
		$this->_isValid = false;
		$this->_validationErrors[$field] = $errorMessage;
	}
	
	public function getValidationError($field)
	{
		// break apart path
		$crumbs = explode('.', $field);

		// resolve path recursively
		$cur = &$this->_validationErrors;
		while($crumb = array_shift($crumbs))
		{
			if(array_key_exists($crumb, $cur))
				$cur = &$cur[$crumb];
			else
				return null;
		}

		// return current value
		return $cur;
	}
    
    public function getValidationErrors()
    {
        return array_filter($this->_validationErrors);
    }
	
	
	public function validate($deep = true)
	{
		$this->_isValid = true;
		$this->_validationErrors = array();
		
		if(!isset($this->_validator))
		{
			$this->_validator = new RecordValidator($this->_record);
		}
		else
		{
			$this->_validator->resetErrors();
		}

        // iterate through validators config
        $validators = static::getStackedConfig('validators');
        if (count(static::getStackedConfig('validators'))) {
            foreach (static::getStackedConfig('validators') AS $validator => $options) {
                if (isset($options['validator']) && is_callable($options['validator'])) {
                    call_user_func($options['validator'], $this->_validator, $this, $options, $validator);
                } else {
                    $this->_validator->validate($options);
                }
            }

            $this->finishValidation();
        }

		// validate related objects
		if($deep)
		{
			foreach(static::getStackedConfig('relationships') AS $relationship => $options)
			{
				if(empty($this->_relatedObjects[$relationship]))
				{
					continue;
				}
				
				
				if($options['type'] == 'one-one')
				{
					if($this->_relatedObjects[$relationship]->isDirty)
					{
						$this->_relatedObjects[$relationship]->validate();
						$this->_isValid = $this->_isValid && $this->_relatedObjects[$relationship]->isValid;
						$validationErrors = $this->_relatedObjects[$relationship]->validationErrors;

						if (count($validationErrors)) {
							$this->_validationErrors[$relationship] = $validationErrors;
						}
					}
				}
				elseif($options['type'] == 'one-many')
				{
					foreach($this->_relatedObjects[$relationship] AS $i => $object)
					{
						if($object->isDirty)
						{
							$object->validate();
							$this->_isValid = $this->_isValid && $object->isValid;
							$validationErrors = $object->validationErrors;

							if (count($validationErrors)) {
								$this->_validationErrors[$relationship][$i] = $validationErrors;
							}
						}
					}
				}
				/*elseif($options['type'] == 'contextual')
				{
					foreach($this->_relatedObjects[$relationship] AS $i => $object)
					{
						if($object->isDirty)
						{
							$object->validate();
							$this->_isValid = $this->_isValid && $object->isValid;
							$this->_validationErrors[$relationship][$i] = $object->validationErrors;
						}					
					}
				}*/
				
			}
		}
		
		return $this->_isValid;
	}
	
	protected function finishValidation()
	{
		$this->_isValid = $this->_isValid && !$this->_validator->hasErrors();
		
		if(!$this->_isValid)
		{
			$this->_validationErrors = array_merge($this->_validationErrors, $this->_validator->getErrors());	
		}

		return $this->_isValid;
	}
	
	public function changeClass($className = false, $fieldValues = false)
	{
		if(!$className)
		{
			$className = $this->Class;
		}
	
		// return if no change needed
		if($className == get_class($this))
		{
			$ActiveRecord = $this;
		}
		else
		{
			$this->_record[static::_cn('Class')] = $className;
			$ActiveRecord = new $className($this->_record, true, $this->isPhantom);
		}
		
		if($fieldValues)
		{
			$ActiveRecord->setFields($fieldValues);
		}
		
		if(!$this->isPhantom)
			$ActiveRecord->save();
		
		return $ActiveRecord;
	}
	
	public function setFields($values)
	{
		foreach($values AS $field => $value)
		{
			$this->_setFieldValue($field, $value);
		}
	}
	
	public function setField($field, $value)
	{
		$this->_setFieldValue($field, $value);
	}
    
    public function getSummary()
	{
		$data = array();
		
		foreach($this->getClassFields() AS $field => $options)
		{
			if(!empty($options['includeInSummary'])) {
				$data[$field] = $this->_getFieldValue($field);
			}
		}
		
		return $data;
	}
    
    public function getData()
	{
		$data = array();
		
		foreach ($this->getClassFields() AS $field => $options) {
			if (empty($options['excludeFromData']) && $this->userCanEnumerateField($field)) {
				$data[$field] = $this->_getFieldValue($field);
			}
		}
		
		return $data;
	}
    
    public function getDetails($include = '*', $stringsOnly = false)
	{
		$data = $this->getData();
		
		if (($include == '*' || in_array('validationErrors', $include)) && $this->validationErrors) {
			$data['validationErrors'] = $this->validationErrors;
		}
        
        foreach (static::getStackedConfig('dynamicFields') AS $field => $options) {
            if ($include != '*' && !in_array($field, $include)) {
                continue;
            }
            
            if (!$this->userCanEnumerateDynamicField($field)) {
                continue;
            }
            
            $method = $options['method'];
            
            if ($method && is_string($method) && method_exists($this, $method)) {
                $value = $this->$method($stringsOnly, $options, $field);
            } elseif($method && is_callable($method)) {
                $value = call_user_func($method, $this, $stringsOnly, $options, $field);
            } elseif(!empty($options['relationship']) && static::_relationshipExists($options['relationship'])) {
                $value = $this->_getRelationshipValue($options['relationship']);
            } else {
                continue;
            }
            
            if ($stringsOnly && !is_string($value)) {
                if (is_array($value)) {
                    $strings = array();
                    foreach ($value AS $key => $attr) {
                        $strings[] = is_string($key) ? "$key=$attr" : $attr;
                    }
                    $value = implode(',', $strings);
                } else {
                    $value = (string)$value;
                }
            }
            
            $data[$field] = $value;
        }
		
		return $data;
	}
	
	public function isFieldDirty($field)
	{
		return $this->isPhantom || array_key_exists($field, $this->_originalValues);
	}
	
	public function getOriginalValue($field)
	{
		return $this->_originalValues[$field];
	}
	
	public function dumpData($exit = false)
	{
		Debug::dump($this->getData(), $exit, get_class($this));
	}
	
	public function save($deep = true)
	{
		// set creator
		if(static::_fieldExists('CreatorID') && !$this->CreatorID && !empty($_SESSION) && !empty($_SESSION['User']))
		{
			$this->CreatorID = $_SESSION['User']->ID;
		}
		
		// set created
		if(static::_fieldExists('Created') && (!$this->Created || ($this->Created == 'CURRENT_TIMESTAMP')))
		{
			$this->Created = time();
		}
		
		// validate
		if(!$this->validate($deep))
		{
			throw new RecordValidationException($this, 'Cannot save invalid record');
		}
		
		// clear caches
		foreach($this->getClassFields() AS $field => $options)
		{
			if(!empty($options['unique']) || !empty($options['primary']))
			{
				$key = sprintf('%s/%s:%s', static::$tableName, $field, $this->getValue($field));
				DB::clearCachedRecord($key);
			}
		}
		
		// traverse relationships
		if($deep)
		{
			$this->_saveRelationships();
		}

		if($this->isDirty)
		{
            if (!$this->_isPhantom && static::$trackModified) {
                $this->Modified = time();
                
                if (!empty($_SESSION) && !empty($_SESSION['User'])) {
                    $this->ModifierID = $_SESSION['User']->ID;
                }
            }
            
			// prepare record values
			$recordValues = $this->_prepareRecordValues();
	
			// transform record to set array
			$set = static::_mapValuesToSet($recordValues);
			
			// create new or update existing
			if($this->_isPhantom)
			{
				try {
					DB::nonQuery(
						'INSERT INTO `%s` SET %s'
						, array(
							static::$tableName
							, join(',', $set)
						)
					);
				} catch(TableNotFoundException $e) {
					// auto-create table and try insert again
					DB::multiQuery(SQL::getCreateTable(get_called_class()));
					
					DB::nonQuery(
						'INSERT INTO `%s` SET %s'
						, array(
							static::$tableName
							, join(',', $set)
						)
					);
				}
				
				$this->_record['ID'] = DB::insertID();
				$this->_isPhantom = false;
				$this->_isNew = true;
			}
			elseif(count($set))
			{
				DB::nonQuery(
					'UPDATE `%s` SET %s WHERE `%s` = %u'
					, array(
						static::$tableName
						, join(',', $set)
						, static::_cn('ID')
						, $this->ID
					)
				);
				
				$this->_isUpdated = true;
			}
			
			// clear cache
			static::_invalidateRecordCaches($this->ID);
			
			// update state
			$this->_isDirty = false;
		}
		
		// traverse relationships again
		if($deep)
		{
			$this->_postSaveRelationships();
		}
	}
	
	protected function _saveRelationships()
	{
		// save relationship objects
		foreach(static::getStackedConfig('relationships') AS $relationship => $options)
		{
			if($options['type'] == 'one-one')
			{
				if(isset($this->_relatedObjects[$relationship]) && $options['local'] != 'ID')
				{
					$this->_relatedObjects[$relationship]->save();
					$this->_setFieldValue($options['local'], $this->_relatedObjects[$relationship]->getValue($options['foreign']));
				}
			}
			elseif($options['type'] == 'one-many')
			{
				if(isset($this->_relatedObjects[$relationship]) && $options['local'] != 'ID')
				{
					foreach($this->_relatedObjects[$relationship] AS $related)
					{
						if($related->isPhantom)
							$related->_setFieldValue($options['foreign'], $this->_getFieldValue($options['local']));
							
						$related->save();
					}
				}
			}
			elseif($options['type'] == 'handle')
			{
				if(isset($this->_relatedObjects[$relationship]))
				{
					$this->_setFieldValue($options['local'], $this->_relatedObjects[$relationship]->Handle);
				}
			}
			else
			{
				// TODO: Implement other methods
			}
			
		}
	}
	
	protected function _postSaveRelationships()
	{
		//die('psr');
		// save relationship objects
		foreach(static::getStackedConfig('relationships') AS $relationship => $options)
		{
			if(!isset($this->_relatedObjects[$relationship]))
			{
				continue;
			}

			if($options['type'] == 'handle')
			{
				$this->_relatedObjects[$relationship]->Context = $this;
				$this->_relatedObjects[$relationship]->save();
			}
			elseif($options['type'] == 'one-one' && $options['local'] == 'ID')
			{
				$this->_relatedObjects[$relationship]->setField($options['foreign'], $this->getValue($options['local']));
				$this->_relatedObjects[$relationship]->save();
			}
			elseif($options['type'] == 'one-many' && $options['local'] == 'ID')
			{
				foreach($this->_relatedObjects[$relationship] AS $related)
				{
					$related->setField($options['foreign'], $this->getValue($options['local']));
					$related->save();
				}
			}
		}
	}
	
	
	public function destroy()
	{
		return $this->_isDestroyed = static::delete($this->ID);
	}
	
	static public function delete($id)
	{
		DB::nonQuery('DELETE FROM `%s` WHERE `%s` = %u', array(
			static::$tableName
			,static::_cn('ID')
			,$id
		));
		
		static::_invalidateRecordCaches($id);
		
		return DB::affectedRows() > 0;
	}
	
	static public function getByContextObject(ActiveRecord $Record, $options = array())
	{
		return static::getByContext($Record->getRootClass(), $Record->ID, $options);
	}
	
	static public function getByContext($contextClass, $contextID, $options = array())
	{
		$options = array_merge(array(
			'conditions' => array()
			,'order' => false
		), $options);
		
		$options['conditions']['ContextClass'] = $contextClass;
		$options['conditions']['ContextID'] = $contextID;
	
		$record = static::getRecordByWhere($options['conditions'], $options);

		$className = static::_getRecordClass($record);
		
		return $record ? new $className($record) : null;
	}
	
	static public function getByHandle($handle)
	{
		return static::fieldExists('Handle') ? static::getByField('Handle', $handle) : static::getByID($handle);
	}
	
	static public function getByID($id)
	{
		$record = static::getRecordByField('ID', $id, true);
		
		return static::instantiateRecord($record);
	}
		
	static public function getByField($field, $value)
	{
		$record = static::getRecordByField($field, $value);
		
		return static::instantiateRecord($record);
	}
	
	static public function getRecordByField($field, $value)
	{
		$query = 'SELECT * FROM `%s` WHERE `%s` = "%s" LIMIT 1';
		$params = array(
			static::$tableName
			, static::_cn($field)
			, DB::escape($value)
		);
	
		try {
			if (static::$useCache) {
				$cacheKey = sprintf('ar/%s/%s:%s', static::$tableName, $field, $value);
		
				if($record = Cache::fetch($cacheKey)) {
					return $record;
				}
			}
		
			$record = DB::oneRecord($query, $params);
		
			if (static::$useCache && !empty($record['ID'])) {
				static::mapDependentCacheKey($record['ID'], $cacheKey);
				Cache::store($cacheKey, $record, 300);
			}
		
			return $record;	
		} catch(TableNotFoundException $e) {
			return null;
		}
	}
	
	static public function getByWhere($conditions, $options = array())
	{
		$record = static::getRecordByWhere($conditions, $options);
		
		return static::instantiateRecord($record);
	}
	
	static public function getRecordByWhere($conditions, $options = array())
	{
		if(!is_array($conditions))
		{
			$conditions = array($conditions);
		}
		
		$options = array_merge(array(
			'order' => false
		), $options);

		// initialize conditions and order
		$conditions = static::_mapConditions($conditions);
		$order = $options['order'] ? static::_mapFieldOrder($options['order']) : array();
		
		try {
			return DB::oneRecord(
				'SELECT * FROM `%s` WHERE (%s) %s LIMIT 1'
				, array(
					static::$tableName
					, join(') AND (', $conditions)
					, $order ? 'ORDER BY '.join(',', $order) : ''
				)
			);
		} catch(TableNotFoundException $e) {
			return null;
		}
	}
	
	static public function getByQuery($query, $params = array())
	{
		return static::instantiateRecord(DB::oneRecord($query, $params));
	}

	static public function getAllByClass($className = false, $options = array())
	{
		return static::getAllByField('Class', $className ? $className : get_called_class(), $options);
	}
	
	static public function getAllByContextObject(ActiveRecord $Record, $options = array())
	{
		return static::getAllByContext($Record->getRootClass(), $Record->ID, $options);
	}

	static public function getAllByContext($contextClass, $contextID, $options = array())
	{
		$options = array_merge(array(
			'conditions' => array()
		), $options);
		
		$options['conditions']['ContextClass'] = $contextClass;
		$options['conditions']['ContextID'] = $contextID;
	
		return static::instantiateRecords(static::getAllRecordsByWhere($options['conditions'], $options));
	}
	
	static public function getAllByField($field, $value, $options = array())
	{
		return static::getAllByWhere(array($field => $value), $options);
	}
		
	static public function getAllByWhere($conditions = array(), $options = array())
	{
		return static::instantiateRecords(static::getAllRecordsByWhere($conditions, $options));
	}
	
	static public function getAllRecordsByWhere($conditions = array(), $options = array())
	{
		$options = array_merge(array(
			'indexField' => false
			,'order' => false
			,'limit' => false
			,'offset' => 0
			,'calcFoundRows' => !empty($options['limit'])
			,'joinRelated' => false
			,'extraColumns' => false
			,'having' => false
		), $options);

		
		// handle joining related tables
		$join = '';
		if($options['joinRelated'])
		{
			if(is_string($options['joinRelated']))
			{
				$options['joinRelated'] = array($options['joinRelated']);
			}
			
			// prefix any conditions
			
			foreach($options['joinRelated'] AS $relationship)
			{
				if(!$rel = static::getStackedConfig('relationships', $relationship))
				{
					die("joinRelated specifies a relationship that does not exist: $relationship");
				}
								
				switch($rel['type'])
				{
					case 'one-one':
					{
						$join .= sprintf(' JOIN `%1$s` AS `%2$s` ON(`%2$s`.`%3$s` = `%4$s`)', $rel['class']::$tableName, $rel['class']::getTableAlias(), $rel['foreign'], $rel['local']);
						break;
					}
					default:
					{
						die("getAllRecordsByWhere does not support relationship type $rel[type]");
					}
				}
			}
		}
		
		// initialize conditions
		if($conditions)
		{
			if(is_string($conditions))
			{
				$conditions = array($conditions);
			}
		
			$conditions = static::_mapConditions($conditions);
		}
		
		// build query
		$query  = 'SELECT %1$s `%3$s`.*';
		
		if(!empty($options['extraColumns']))
		{
			if(is_array($options['extraColumns']))
			{
				foreach($options['extraColumns'] AS $key => $value)
				{
					$query .= ', '.$value.' AS '.$key;
				}
			}
			else
			{
				$query .= ', ' . $options['extraColumns'];
			}
		}
		$query .= ' FROM `%2$s` AS `%3$s` %4$s';
		$query .= ' WHERE (%5$s)';
		
		if(!empty($options['having']))
		{
			$query .= ' HAVING (' . (is_array($options['having']) ? join(') AND (', static::_mapConditions($options['having'])) : $options['having']) . ')';
		}
		
		$params = array(
			$options['calcFoundRows'] ? 'SQL_CALC_FOUND_ROWS' : ''
			, static::$tableName
			, static::getTableAlias()
			, $join
			, $conditions ? join(') AND (', $conditions) : '1'
		);
		
		

		if($options['order'])
		{
			$query .= ' ORDER BY ' . join(',', static::_mapFieldOrder($options['order']));
		}
		
		if($options['limit'])
		{
			$query .= sprintf(' LIMIT %u,%u', $options['offset'], $options['limit']);
		}
		
		try {
			if($options['indexField'])
			{
				return DB::table(static::_cn($options['indexField']), $query, $params);
			}
			else
			{
				return DB::allRecords($query, $params);
			}
		} catch(TableNotFoundException $e) {
			return array();
		}
	}
	
	static public function getAll($options = array())
	{
		return static::instantiateRecords(static::getAllRecords($options));
	}
	
	static public function getAllRecords($options = array())
	{
		$options = array_merge(array(
			'indexField' => false
			,'order' => false
			,'limit' => false
			,'offset' => 0
		), $options);
		
		$query = 'SELECT * FROM `%s`';
		$params = array(
			static::$tableName
		);
		
		if($options['order'])
		{
			$query .= ' ORDER BY ' . join(',', static::_mapFieldOrder($options['order']));
		}
		
		if($options['limit'])
		{
			$query .= sprintf(' LIMIT %u,%u', $options['offset'], $options['limit']);
		}
		
		try {
			if($options['indexField'])
			{
				return DB::table(static::_cn($options['indexField']), $query, $params);
			}
			else
			{
				return DB::allRecords($query, $params);
			}
		} catch(TableNotFoundException $e) {
			return array();
		}
	}
	
	static public function getAllByQuery($query, $params = array())
	{
		try {
			return static::instantiateRecords(DB::allRecords($query, $params));
		} catch(TableNotFoundException $e) {
			return array();
		}
	}

	static public function getTableByQuery($keyField, $query, $params = array())
	{
		try {
			return static::instantiateRecords(DB::table($keyField, $query, $params));
		} catch(TableNotFoundException $e) {
			return array();
		}
	}

	static public function getCount($conditions = array())
	{
		// initialize conditions
		if($conditions)
		{
			if(is_string($conditions))
			{
				$conditions = array($conditions);
			}
		
			$conditions = static::_mapConditions($conditions);
		}
		
		try {
			return DB::oneValue('SELECT COUNT(*) FROM `%s` WHERE (%s)', array(
				static::$tableName
				,$conditions ? join(') AND (', $conditions) : '1'
			));
		} catch(TableNotFoundException $e) {
			return 0;
		}
	}
	
	
	static public function instantiateRecord($record)
	{
		$className = static::_getRecordClass($record);
		return $record ? new $className($record) : null;
	}
	
	static public function instantiateRecords($records)
	{
		foreach($records AS &$record)
		{
			$className = static::_getRecordClass($record);
			$record = new $className($record);
		}
		
		return $records;
	}
	
	static public function getSqlSearchConditions($qualifier, $term)
	{
		$sqlSearchConditions = array(
			'conditions' => array()
			,'joins' => array()
			,'qualifierFound' => false
		);
		
		foreach(static::aggregateStackedConfig('searchConditions') AS $k => $condition)
		{		
			if(!in_array($qualifier, $condition['qualifiers']))
				continue;
				
			if(!$sqlSearchConditions['qualifierFound'])
			{
				$sqlSearchConditions['qualifierFound'] = true;
			}
			
			if(!empty($condition['join']))
			{
				$joinConditions = $condition['join'];

				$sqlSearchConditions['joins'][] = 'JOIN `'.$joinConditions['className']::$tableName.'` '
					.$joinConditions['aliasName']
					.' ON ('
					.$joinConditions['aliasName'].'.'.$joinConditions['foreignField']
					.' = '
					.get_called_class().'.'.$joinConditions['localField']
					.')';
			}
			
			$callback = !empty($condition['callback']) ? $condition['callback'] : false;
			$sqlCondition = !empty($condition['sql']) ? sprintf($condition['sql'], DB::escape($term)) : false;
			
			if($callback && !$sqlCondition)
			{
                if (is_string($callback)) {
				    $sqlCondition = static::$callback($term, $condition);
			    } elseif (is_callable($callback)) {
    		        $sqlCondition = call_user_func($callback, $term, $condition);   
			    }
			}
			
			if($sqlCondition)
			{
				$sqlSearchConditions['conditions'][] = array(
					'condition' => $sqlCondition
					,'points' => $condition['points']
	                ,'qualifier' => $qualifier
				);
			}
		}
		
		return $sqlSearchConditions;
	}

	// protected methods
	

	
	/**
	 * Called after _defineFields to initialize and apply defaults to the fields property
	 * Must be idempotent as it may be applied multiple times up the inheritence chain
	 */
    static protected function _initFields($config)
	{
        $fields = array();
        
		// apply defaults to relationship definitions
		foreach ($config AS $field => $options) {
            if (!$options) {
                continue;
            }
            
            if (is_string($field)) {
                $fields[$field] = static::_initField($field, is_array($options) ? $options : array('type' => $options));
            } elseif (is_string($options)) {
                $field = $options;
                $fields[$field] = static::_initField($field);
            }
		}
        
        return $fields;
	}
    
	static protected function _initField($field, $options = array())
	{
		$options = array_merge(array(
			'type' => null
			,'length' => null
			,'primary' => null
			,'unique' => null
			,'autoincrement' => null
			,'notnull' => null
			,'unsigned' => null
			,'default' => null
			,'values' => null
		), static::$fieldDefaults, array('columnName' => $field), $options);

		if ($field == 'Class') {
			// apply Class enum values
			$options['values'] = static::getStaticSubClasses();
		}
		
		if (!isset($options['blankisnull']) && empty($options['notnull'])) {
			$options['blankisnull'] = true;
		}
		
		if ($options['autoincrement']) {
			$options['primary'] = true;
		}
        
        if (empty($options['label'])) {
            $options['label'] = Inflector::labelIdentifier($field);
        }
        
        return $options;
	}
	
	static protected function _linkFieldToRelationship($field, $relationship)
	{
		$fieldOptions = &static::getFieldOptions($field);
		$fieldOptions['relationships'][$relationship] = true;
	}

	
	/**
	 * Called after _defineRelationships to initialize and apply defaults to the relationships property
	 * Must be idempotent as it may be applied multiple times up the inheritence chain
	 */
	static protected function _initRelationships($relationships)
	{
		// apply defaults to relationship definitions
		foreach ($relationships AS $relationship => &$options) {
			$options = static::_initRelationship($relationship, $options);
		}
        
        return $relationships;
	}
	
	
	static protected function _initRelationship($relationship, $options)
	{
		// sanity checks
		$className = get_called_class();
		
		if(is_string($options))
		{
			$options = array(
				'type' => 'one-one'
				,'class' => $options
			);
		}
		
		if(!is_string($relationship) || !is_array($options))
		{
			die('Relationship must be specified as a name => options pair');
		}
		
		// apply defaults
		if(empty($options['type']))
		{
			$options['type'] = 'one-one';
		}
		
		if($options['type'] == 'one-one')
		{
			if(empty($options['local']))
				$options['local'] = $relationship . 'ID';
				
			if(empty($options['foreign']))
				$options['foreign'] = 'ID';	
				
			if(!isset($options['conditions']))
				$options['conditions'] = array();			
		}
		elseif($options['type'] == 'one-many')
		{
			if(empty($options['local']))
				$options['local'] = 'ID';
					
			if(empty($options['foreign']))
				$options['foreign'] = static::getStaticRootClass() . 'ID';
				
			if(!isset($options['indexField']))
				$options['indexField'] = false;
				
			if(!isset($options['conditions']))
				$options['conditions'] = array();
			elseif(is_string($options['conditions']))
				$options['conditions'] = array($options['conditions']);
				
			if(!isset($options['order']))
				$options['order'] = false;
		}
		elseif($options['type'] == 'context-children')
		{
			if(empty($options['local']))
				$options['local'] = 'ID';	
					
			if(empty($options['contextClass']))
				$options['contextClass'] = static::getStaticRootClass();
				
			if(!isset($options['indexField']))
				$options['indexField'] = false;
				
			if(!isset($options['conditions']))
				$options['conditions'] = array();
				
			if(!isset($options['order']))
				$options['order'] = false;
		}
		elseif($options['type'] == 'context-child')
		{
			if(empty($options['local']))
				$options['local'] = 'ID';	
					
			if(empty($options['contextClass']))
				$options['contextClass'] = static::getStaticRootClass();
				
			if(!isset($options['indexField']))
				$options['indexField'] = false;
				
			if(!isset($options['conditions']))
				$options['conditions'] = array();
				
			if(!isset($options['order']))
				$options['order'] = array('ID' => 'DESC');
		}
		elseif($options['type'] == 'context-parent')
		{
			if(empty($options['local']))
				$options['local'] = 'ContextID';	
					
			if(empty($options['foreign']))
				$options['foreign'] = 'ID';

			if(empty($options['classField']))
				$options['classField'] = 'ContextClass';
		}
		elseif($options['type'] == 'handle')
		{
			if(empty($options['local']))
				$options['local'] = 'Handle';	

			if(empty($options['class']))
				$options['class'] = 'GlobalHandle';

		}
		elseif($options['type'] == 'many-many')
		{
			if(empty($options['class']))
				die('required many-many option "class" missing');
		
			if(empty($options['linkClass']))
				die('required many-many option "linkClass" missing');
				
			if(empty($options['linkLocal']))
				$options['linkLocal'] = static::getStaticRootClass() . 'ID';
		
			if(empty($options['linkForeign']))
				$options['linkForeign'] = $options['class']::getStaticRootClass() . 'ID';
		
			if(empty($options['local']))
				$options['local'] = 'ID';	

			if(empty($options['foreign']))
				$options['foreign'] = 'ID';	

			if(!isset($options['indexField']))
				$options['indexField'] = false;
				
			if(!isset($options['conditions']))
				$options['conditions'] = array();
				
			if(!isset($options['order']))
				$options['order'] = false;
		}
				
		return $options;	
	}
    
    static protected function _initDynamicFields($config)
    {
        $dynamicFields = array();
        
    	// apply defaults to relationship definitions
		foreach ($config AS $field => $options) {
            if (!$options) {
                continue;
            }
            
            if (is_string($field)) {
                $dynamicFields[$field] = static::_initDynamicField($field, is_array($options) ? $options : array('relationship' => $options));
            } elseif (is_string($options)) {
                $field = $options;
                $dynamicFields[$field] = static::_initDynamicField($field);
            }
		}
        
        return $dynamicFields;
	}
    
    static protected function _initDynamicField($field, $options = array())
    {
        if (empty($options['label'])) {
            $options['label'] = Inflector::labelIdentifier($field);
        }
        
        if (empty($options['method']) && empty($options['relationship'])) {
            $options['relationship'] = $field;
        }
        
        return $options;
    }
    
    static protected function _initValidators($config)
    {
        $validators = array();
        
    	// apply defaults to relationship definitions
		foreach ($config AS $validator => $options) {
            if (!$options) {
                continue;
            }
            
            if (is_string($validator)) {
                $validators[$validator] = static::_initValidator($validator, is_array($options) ? $options : array('field' => $options));
            } elseif (is_string($options)) {
                $validator = $options;
                $validators[$validator] = static::_initValidator($validator);
            }
		}
        
        return $validators;
	}
    
    static protected function _initValidator($validator, $options = array())
    {
        if (empty($options['field']) && (empty($options['validator']) || !is_callable($options['validator']))) {
            $options['field'] = $validator;
        }
        
        return $options;
    }


	/**
	 * Returns class name for instantiating given record
	 * @param array $record record
	 * @return string class name
	 */
	static protected function _getRecordClass($record)
	{
		$static = get_called_class();
		
		if(!static::_fieldExists('Class'))
		{
			return $static;
		}
		
		$columnName = static::_cn('Class');
		
		if(!empty($record[$columnName]))
		{
			return $record[$columnName];
		}
		else
		{		
			return $static;
		} 
	}
	
	static protected function _fieldExists($field)
	{
		$fields = static::getStackedConfig('fields');
		return array_key_exists($field, $fields);
	}
	
	static public function fieldExists($field) { return static::_fieldExists($field); }


	static protected function _relationshipExists($relationship)
	{
		$relationships = static::getStackedConfig('relationships');
		return array_key_exists($relationship, $relationships);
	}
    
	static public function relationshipExists($relationship) { return static::_relationshipExists($relationship); }
	
	
	static public function getClassFields()
	{
		return static::getStackedConfig('fields');
	}
	
	static public function &getFieldOptions($field, $optionKey = false)
	{
	    $fieldOptions = &static::getStackedConfig('fields', $field);
	    
        if ($optionKey) {
            return $fieldOptions[$optionKey];
        } else {
            return $fieldOptions;
        }
	}

	/**
	 * Returns columnName for given field
	 * @param string $field name of field
	 * @return string column name
	 */
	static public function getColumnName($field)
	{
		if(!static::_fieldExists($field))
		{
			throw new Exception('getColumnName called on nonexisting column: ' . get_called_class().'->'.$field);
		}
		
		return static::getFieldOptions($field, 'columnName');
	}
	
	/**
	 * Shorthand alias for _getColumnName
	 * @param string $field name of field
	 * @return string column name
	 */
	static protected function _cn($field) { return static::getColumnName($field); }

	
	/**
	 * Retrieves given field's value
	 * @param string $field Name of field
	 * @return mixed value
	 */
	protected function _getFieldValue($field, $useDefault = true)
	{
		$fieldOptions = static::getFieldOptions($field);
	
		if(isset($this->_record[$fieldOptions['columnName']]))
		{
			$value = $this->_record[$fieldOptions['columnName']];
			
			// apply type-dependent transformations
			switch($fieldOptions['type'])
			{
				case 'timestamp':
				{
					if(!isset($this->_convertedValues[$field]))
					{
						if($value && $value != '0000-00-00 00:00:00')
							$this->_convertedValues[$field] = strtotime($value);
						else
							$this->_convertedValues[$field] = null;
					}
					
					return $this->_convertedValues[$field];
				}
				case 'json':
				{
					if(!isset($this->_convertedValues[$field]))
					{
						$this->_convertedValues[$field] = is_string($value) ? json_decode($value, true) : $value;
					}
					
					return $this->_convertedValues[$field];
				}
				case 'serialized':
				{
					if(!isset($this->_convertedValues[$field]))
					{
						$this->_convertedValues[$field] = is_string($value) ? unserialize($value) : $value;
					}
					
					return $this->_convertedValues[$field];
				}
				case 'set':
				case 'list':
				{
					if(!isset($this->_convertedValues[$field]))
					{
						$delim = empty($fieldOptions['delimiter']) ? ',' : $fieldOptions['delimiter'];
						$this->_convertedValues[$field] = array_filter(preg_split('/\s*'.$delim.'\s*/', $value));
					}
					
					return $this->_convertedValues[$field];
				}
				
				case 'boolean':
				{
					if(!isset($this->_convertedValues[$field]))
					{
						$this->_convertedValues[$field] = (boolean)$value;
					}
					
					return $this->_convertedValues[$field];
				}
				
				
	        	case 'int':
	    		case 'uint':
				case 'integer':
				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'bigint':
				{
					if(!isset($this->_convertedValues[$field]))
					{
						$this->_convertedValues[$field] = (integer)$value;
					}
					
					return $this->_convertedValues[$field];
				}
				
				default:
				{
					return $value;
				}
			}
		}
		elseif($useDefault && isset($fieldOptions['default']))
		{
			// return default
			return $fieldOptions['default'];
		}
		else
		{
			switch($fieldOptions['type'])
			{
				case 'set':
				case 'list':
				{
					return array();
				}
				default:
				{
					return null;
				}
			}
		}
	}
	
	/**
	 * Sets given field's value
	 * @param string $field Name of field
	 * @param mixed $value New value
	 * @return mixed value
	 */
	protected function _setFieldValue($field, $value)
	{
		// ignore overwriting meta fields
		if(in_array($field, array('Created','CreatorID')) && $this->_getFieldValue($field, false))
		{
			return false;
		}
		
		if(!static::_fieldExists($field))
		{
			// set relationship
			if(static::_relationshipExists($field))
			{
				return $this->_setRelationshipValue($field, $value);
			}
			else
			{
				return false;
			}
		}
        
        if (!$this->userCanWriteField($field)) {
            throw new Exception("Access denied for current user to write field '$field'");
        }
        
		$fieldOptions = static::getFieldOptions($field);
        $originalValue = $this->_getFieldValue($field);

		// no overriding autoincrements
		if($fieldOptions['autoincrement'])
		{
			return false;
		}

		// pre-process value
		$forceDirty = false;
		switch($fieldOptions['type'])
		{
			case 'clob':
			case 'string':
			{
				if(!$fieldOptions['notnull'] && $fieldOptions['blankisnull'] && ($value === '' || $value === NULL))
				{
					$value = null;
					break;
				}
			
				// normalize encoding to ASCII
				$value = @mb_convert_encoding($value, DB::$encoding, 'auto');
				
				// remove any remaining non-printable characters
				//$value = preg_replace('/[^[:print:][:space:]]/', '', $value);
				
				break;
			}
			
			case 'boolean':
			{
				$this->_convertedValues[$field] = (boolean)$value;

                $value = $this->_convertedValues[$field] ? '1' : '0';
			}
			
			case 'decimal':
			{
				$value = preg_replace('/(.)-/', '$1', preg_replace('/[^-\d.]/','', $value));
				break;
			}
			case 'json':
			{
				$this->_convertedValues[$field] = $value;
				$value = json_encode($value);
				break;
			}
			
        	case 'int':
    		case 'uint':
			case 'integer':
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
			{
				if (!$fieldOptions['notnull'] && ($value === '' || $value === null)) {
					$this->_convertedValues[$field] = $value = NULL;
				} else {
                    $value = round($value);
                    $this->_convertedValues[$field] = $value;
					$value = (string)$value;
				}
				
				break;
			}
			
			case 'timestamp':
			{
				if(is_numeric($value))
				{
					$value = date('Y-m-d H:i:s', $value);
				}
				elseif(is_string($value))
				{
					// trim any extra crap, or leave as-is if it doesn't fit the pattern
					$value = preg_replace('/^(\d{4})\D?(\d{2})\D?(\d{2})T?(\d{2})\D?(\d{2})\D?(\d{2})/', '$1-$2-$3 $4:$5:$6', $value);
				}

                unset($this->_convertedValues[$field]);

				break;
			}
			
			case 'date':
			{	
				if(is_numeric($value))
				{
					$value = date('Y-m-d', $value);
				}
				elseif(is_string($value))
				{
					// trim time and any extra crap, or leave as-is if it doesn't fit the pattern
					$value = preg_replace('/^(\d{4})\D?(\d{2})\D?(\d{2}).*/', '$1-$2-$3', $value);
				}
				elseif(is_array($value) && count(array_filter($value)))
				{
					// collapse array date to string
					$value = sprintf(
						'%04u-%02u-%02u'
						,is_numeric($value['yyyy']) ? $value['yyyy'] : 0
						,is_numeric($value['mm']) ? $value['mm'] : 0
						,is_numeric($value['dd']) ? $value['dd'] : 0
					);
				}
				else
				{
					$value = null;
				}
				break;
			}
			
			// these types are converted to strings from another PHP type on save
			case 'serialized':
			{
				$this->_convertedValues[$field] = $value;
				$value = serialize($value);
				break;
			}
			case 'set':
			case 'list':
			{
				if(!is_array($value))
				{
					$delim = empty($fieldOptions['delimiter']) ? ',' : $fieldOptions['delimiter'];
					$value = array_filter(preg_split('/\s*'.$delim.'\s*/', $value));
				}
			
				$this->_convertedValues[$field] = $value;
				$forceDirty = true;
				break;
			}

		}
		$columnName = static::_cn($field);

		if($forceDirty || !array_key_exists($columnName, $this->_record) || $this->_record[$columnName] !== $value)
		{
			if(array_key_exists($columnName, $this->_record))
			{
				$this->_originalValues[$field] = $originalValue;
			}
			$this->_record[$columnName] = $value;
			$this->_isDirty = true;
			
			// unset invalidated relationships
			if(!empty($fieldOptions['relationships']))
			{
				foreach($fieldOptions['relationships'] AS $relationship => $isCached)
				{
					if($isCached)
					{
						unset($this->_relatedObjects[$relationship]);
					}
				}
			}
			
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Retrieves given relationships' value
	 * @param string $relationship Name of relationship
	 * @return mixed value
	 */
	protected function _getRelationshipValue($relationship)
	{
		if(!isset($this->_relatedObjects[$relationship]))
		{
			$rel = static::getStackedConfig('relationships', $relationship);

			if($rel['type'] == 'one-one')
			{
				if($value = $this->_getFieldValue($rel['local']))
				{
					$conditions = is_callable($rel['conditions']) ? call_user_func($rel['conditions'], $this, $relationship, $rel, $value) : $rel['conditions'];
					
					if (!empty($conditions)) {
						$conditions[$rel['foreign']] = $value;
						$this->_relatedObjects[$relationship] = $rel['class']::getByWhere($conditions);
					} else {
						// use cachable single-field lookup
						$this->_relatedObjects[$relationship] = $rel['class']::getByField($rel['foreign'], $value);
					}
				
					// hook relationship for invalidation
					static::_linkFieldToRelationship($rel['local'], $relationship);
				}
				else
				{
					$this->_relatedObjects[$relationship] = null;
				}
			}
			elseif($rel['type'] == 'one-many')
			{
				if(!empty($rel['indexField']) && !$rel['class']::_fieldExists($rel['indexField']))
				{
					$rel['indexField'] = false;
				}
				
				$this->_relatedObjects[$relationship] = $rel['class']::getAllByWhere(
					array_merge(
						is_callable($rel['conditions']) ? call_user_func($rel['conditions'], $this, $relationship, $rel) : $rel['conditions']
						,array(
							$rel['foreign'] => $this->_getFieldValue($rel['local'])
						)
					)
					, array(
						'indexField' => $rel['indexField']
						,'order' => $rel['order']
						,'conditions' => $rel['conditions']
					)
				);
				
				
				// hook relationship for invalidation
				static::_linkFieldToRelationship($rel['local'], $relationship);
			}
			elseif($rel['type'] == 'context-children')
			{
				if(!empty($rel['indexField']) && !$rel['class']::_fieldExists($rel['indexField']))
				{
					$rel['indexField'] = false;
				}
				
				$conditions = array_merge(is_callable($rel['conditions']) ? call_user_func($rel['conditions'], $this, $relationship, $rel) : $rel['conditions'], array(
					'ContextClass' => $rel['contextClass']
					,'ContextID' => $this->_getFieldValue($rel['local'])
				));
			
				$this->_relatedObjects[$relationship] = $rel['class']::getAllByWhere(
					$conditions
					, array(
						'indexField' => $rel['indexField']
						,'order' => $rel['order']
					)
				);
				
				// hook relationship for invalidation
				static::_linkFieldToRelationship($rel['local'], $relationship);
			}
			elseif($rel['type'] == 'context-child')
			{
				$conditions = array_merge(is_callable($rel['conditions']) ? call_user_func($rel['conditions'], $this, $relationship, $rel) : $rel['conditions'], array(
					'ContextClass' => $rel['contextClass']
					,'ContextID' => $this->_getFieldValue($rel['local'])
				));
			
				$this->_relatedObjects[$relationship] = $rel['class']::getByWhere(
					$conditions
					, array(
						'order' => $rel['order']
					)
				);
			}
			elseif($rel['type'] == 'context-parent')
			{
				$className = $this->_getFieldValue($rel['classField']);
				$this->_relatedObjects[$relationship] = $className ? $className::getByID($this->_getFieldValue($rel['local'])) : null;
				
				// hook both relationships for invalidation
				static::_linkFieldToRelationship($rel['classField'], $relationship);
				static::_linkFieldToRelationship($rel['local'], $relationship);
			}
			elseif($rel['type'] == 'handle')
			{
				if($handle = $this->_getFieldValue($rel['local']))
				{
					$this->_relatedObjects[$relationship] = $rel['class']::getByHandle($handle);
				
					// hook relationship for invalidation
					static::_linkFieldToRelationship($rel['local'], $relationship);
				}
				else
				{
					$this->_relatedObjects[$relationship] = null;
				}
			}
			elseif($rel['type'] == 'many-many')
			{				
				if(!empty($rel['indexField']) && !$rel['class']::_fieldExists($rel['indexField']))
				{
					$rel['indexField'] = false;
				}
				
				$conditions = is_callable($rel['conditions']) ? call_user_func($rel['conditions'], $this, $relationship, $rel) : $rel['conditions'];
				
				// TODO: support indexField, conditions, and order
				$query = 'SELECT Related.* FROM `%s` Link JOIN `%s` Related ON (Related.`%s` = Link.%s) WHERE Link.`%s` = %u AND %s';
				$params = array(
					$rel['linkClass']::$tableName
					,$rel['class']::$tableName
					,$rel['foreign']
					,$rel['linkForeign']
					,$rel['linkLocal']
					,$this->_getFieldValue($rel['local'])
					,$conditions ? join(' AND ', $conditions) : '1'
				);
				
				if($rel['indexField']) {
					$this->_relatedObjects[$relationship] = $rel['class']::getTableByQuery($rel['class']::_cn($rel['indexField']), $query, $params);
				}
				else {
					$this->_relatedObjects[$relationship] = $rel['class']::getAllByQuery($query, $params);
				}
				
				// hook relationship for invalidation
				static::_linkFieldToRelationship($rel['local'], $relationship);
			}
		}
		
		return $this->_relatedObjects[$relationship];
	}
	
	
	protected function _setRelationshipValue($relationship, $value)
	{
		$rel = static::getStackedConfig('relationships', $relationship);
				
		if($rel['type'] ==  'one-one')
		{
			if($value !== null && !is_a($value,'ActiveRecord'))
			{
				return false;
			}
			
			if($rel['local'] != 'ID')
			{
				$this->_setFieldValue($rel['local'], $value ? $value->getValue($rel['foreign']) : null);
			}
		}
		elseif($rel['type'] ==  'context-parent')
		{
			if($value !== null && !is_a($value,'ActiveRecord'))
			{
				return false;
			}

			if(empty($value))
			{
				// set Class and ID
				$this->_setFieldValue($rel['classField'], null);
				$this->_setFieldValue($rel['local'], null);
			}
			else
			{
				// set Class and ID
				$this->_setFieldValue($rel['classField'], $value->getRootClass());
				$this->_setFieldValue($rel['local'], $value->__get($rel['foreign']));
			}

		}
		elseif($rel['type'] == 'one-many' && is_array($value))
		{
			$set = array();
			
			foreach($value AS $related)
			{
				if(!$related || !is_a($related,'ActiveRecord')) continue;
				
				$related->_setFieldValue($rel['foreign'], $this->_getFieldValue($rel['local']));
				$set[] = $related;
			}
			
			// so any invalid values are removed
			$value = $set;
		}
		elseif($rel['type'] ==  'handle')
		{
			if($value !== null && !is_a($value,'ActiveRecord'))
			{
				return false;
			}
			
			$this->_setFieldValue($rel['local'], $value ? $value->Handle : null);
		}
		else
		{
			return false;
		}

		$this->_relatedObjects[$relationship] = $value;
		$this->_isDirty = true;
	}
	
	public function appendRelated($relationship, $values)
	{
		$rel = static::getStackedConfig('relationships', $relationship);
		
		if($rel['type'] != 'one-many')
		{
			throw new Exception('Can only append to one-many relationship');
		}
		
		if(!is_array($values))
		{
			$values = array($values);
		}
		
		foreach($values AS $relatedObject)
		{
			if(!$relatedObject || !is_a($relatedObject,'ActiveRecord')) continue;
			
			$relatedObject->_setFieldValue($rel['foreign'], $this->_getFieldValue($rel['local']));
			$this->_relatedObjects[$relationship][] = $relatedObject;
			$this->_isDirty = true;
		}
	}

	protected function _prepareRecordValues()
	{
		$record = array();

		foreach(static::getStackedConfig('fields')AS $field => $options)
		{
			$columnName = static::_cn($field);
			
			if(array_key_exists($columnName, $this->_record))
			{
				$value = $this->_record[$columnName];
				
				if(!$value && !empty($options['blankisnull']))
				{
					$value = null;
				}
			}
			elseif(isset($options['default']))
			{
				$value = $options['default'];
			}
			else
			{
				continue;
			}
            
            if ($value === null && !empty($options['notnull']) && isset($options['default'])) {
                $value = $options['default'];
            }

			if( ($options['type'] == 'date') && ($value == '0000-00-00') && !empty($options['blankisnull']))
			{
				$value = null;
			}
			if( ($options['type'] == 'timestamp'))
			{
				if(is_numeric($value))
				{
					$value = date('Y-m-d H:i:s', $value);
				}
				elseif($value == '0000-00-00 00:00:00')
				{
					$value = null;
				}
			}

			if( ($options['type'] == 'serialized') && !is_string($value))
			{
				$value = serialize($value);
			}
			
			if( ($options['type'] == 'list') && is_array($value))
			{
				$delim = empty($options['delimiter']) ? ',' : $options['delimiter'];
				$value = implode($delim, $value);
			}
			
			$record[$field] = $value;
		}

		return $record;
	}
	
	static protected function _mapValuesToSet($recordValues)
	{
		$set = array();

		foreach($recordValues AS $field => $value)
		{
			$fieldConfig = static::getFieldOptions($field);
			
			if($value === null)
				$set[] = sprintf('`%s` = NULL', $fieldConfig['columnName']);
			elseif($fieldConfig['type'] == 'timestamp' && $value == 'CURRENT_TIMESTAMP')
				$set[] = sprintf('`%s` = CURRENT_TIMESTAMP', $fieldConfig['columnName']);
			elseif($fieldConfig['type'] == 'set' && is_array($value))
				$set[] = sprintf('`%s` = "%s"', $fieldConfig['columnName'], DB::escape(join(',', $value)));
			elseif($fieldConfig['type'] == 'boolean')
				$set[] = sprintf('`%s` = %u', $fieldConfig['columnName'], $value ? 1 : 0);
			else
				$set[] = sprintf('`%s` = "%s"', $fieldConfig['columnName'], DB::escape($value));
		}

		return $set;
	}
	
	static public function mapFieldOrder($order)
	{
		return static::_mapFieldOrder($order);
	}

	static protected function _mapFieldOrder($order)
	{
		if(is_string($order))
		{
			return array($order);
		}
		elseif(is_array($order))
		{
			$r = array();
			
			foreach($order AS $key => $value)
			{
				if(is_string($key))
				{
					$columnName = static::_cn($key);
					$direction = strtoupper($value)=='DESC' ? 'DESC' : 'ASC';
				}
				else
				{
					$columnName = static::_cn($value);
					$direction = 'ASC';
				}
				
				$r[] = sprintf('`%s` %s', $columnName, $direction);
			}
			
			return $r;
		}
	}
	
	static public function mapConditions($conditions)
	{
		return static::_mapConditions($conditions);
	}
	
	static protected function _mapConditions($conditions)
	{
		
		foreach($conditions AS $field => &$condition)
		{
		
			if(is_string($field))
			{
				$fieldOptions = static::getFieldOptions($field);
			
				if ($condition === null || ($condition == '' && $fieldOptions['blankisnull'])) {
					$condition = sprintf('`%s` IS NULL', static::_cn($field));
				} elseif (is_array($condition)) {
					if (is_array($condition['values'])) {
						$condition = sprintf('`%s` %s ("%s")' ,static::_cn($field), ($condition['operator'] ? $condition['operator'] : 'IN'), implode('", "', DB::escape($condition['values'])));
					} else {
						$condition = sprintf('`%s` %s "%s"', static::_cn($field), $condition['operator'], DB::escape($condition['value']));
					}
				} else {
					$condition = sprintf('`%s` = "%s"', static::_cn($field), DB::escape($condition));
				}
			}
			
		}
		
		return $conditions;
	}
	
	
	static public function getNoun($count = 1)
	{
		return ($count == 1) ? static::$singularNoun : static::$pluralNoun;
	}
	
	public function getRootClass($boundingParentClass = __CLASS__)
	{
		if(static::$rootClass)
		{
			return static::$rootClass;
		}
		
		// detect root class by crawling up the inheritence tree until an ActiveRecord parent is found
		$class = get_called_class();
		while($parentClass = get_parent_class($class))
		{
			if($parentClass == $boundingParentClass)
			{
				return $class;
			}
			
			$class = $parentClass;
		}		
	}
	
	static public function getStaticRootClass($boundingParentClass = __CLASS__)
	{
		if(static::$rootClass)
		{
			return static::$rootClass;
		}
		
		// detect root class by crawling up the inheritence tree until an ActiveRecord parent is found
		$class = get_called_class();
		while($parentClass = get_parent_class($class))
		{
			if($parentClass == $boundingParentClass)
			{
				return $class;
			}
			
			$class = $parentClass;
		}		
	}
	
	public function getDefaultClass()
	{
		if(static::$defaultClass)
		{
			return static::$defaultClass;
		}
		
		return $this->getRootClass();
	}
	
	static public function getStaticDefaultClass()
	{
		if(static::$defaultClass)
		{
			return static::$defaultClass;
		}
		
		return static::getStaticRootClass();
	}
	
	public function getSubClasses()
	{
		if(static::$subClasses)
		{
			return static::$subClasses;
		}
		
		return array_unique(array($this->getRootClass(), get_called_class()));
	}
	
	static public function getStaticSubClasses()
	{
		if(static::$subClasses)
		{
			return static::$subClasses;
		}
		
		return array_unique(array(static::getStaticRootClass(), get_called_class()));
	}
	
	static public function sorterExists($name)
	{
        $sorters = static::getStackedConfig('sorters');
		return array_key_exists($name, $sorters);
	}
	
	static public function &getSorter($name)
	{
		return static::getStackedConfig('sorters', $name);
	}
	
	static protected function _invalidateRecordCaches($recordID)
	{
		if (!static::$useCache) {
			return;
		}
		
		// clear mapped caches
		$cacheMapKey = sprintf('ar/%s/m/%s', static::$tableName, $recordID);
		$cacheMap = Cache::fetch($cacheMapKey);
	
		if (is_array($cacheMap)) {
			foreach ($cacheMap AS $cacheKey) {
				Cache::delete($cacheKey);
			}
			Cache::delete($cacheMapKey);
		}
	}

	static public function mapDependentCacheKey($recordID, $cacheKey)
	{
		$cacheMapKey = sprintf('ar/%s/m/%s', static::$tableName, $recordID);
		$cacheMap = Cache::fetch($cacheMapKey);

		if (is_array($cacheMap)) {
			if (!in_array($cacheKey, $cacheMap)) {
				$cacheMap[] = $cacheKey;
			}
		} else {
			$cacheMap = array($cacheKey);
		}

		Cache::store($cacheMapKey, $cacheMap);
	}

    static public function getTableAlias()
    {
        return str_replace('\\', '_', static::getStaticRootClass());
    }
}