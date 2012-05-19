<?php



 abstract class DatabaseRecord
{

	// configurables
	public static $DefaultConfig = array(
		'TableName' => 'table'
		, 'IDColumn' => 'id'
		, 'SingularNoun' => 'thing'
		, 'PluralNoun' => 'things'
		, 'CacheNamespace' => false
	);
	
	
	// protected properties
	protected static $_config = array();
	protected $_record;
	
	
	// magic methods
	function __construct($record, $className = __CLASS__)
	{
		// initialize record
		if(is_numeric($record))
		{
			$this->_record = self::getRecordByID($record, $className);
			
			if(!$this->_record)
			{
				throw new RecordNotFoundException(sprintf('%s %u does not exist', ucfirst($this->getConfig('SingularNoun')), $record));
			}
		}
		elseif(is_array($record))
		{
			// record provided
			$this->_record = $record;
		}
		else
		{
			throw new Exception(sprintf('Missing or invalid %s record', $this->getConfig('SingularNoun')));
		}
	}
	
	function __get($name)
	{
		switch($name)
		{
			case 'ID':
				return $this->_record[$this->getConfig('IDColumn')];
				
			case 'Data':
			case 'SummaryData':
			case 'JsonTranslation':
				return array($this->getConfig('IDColumn') => $this->ID);
				
			default:
				return null;
		}
	}
	
	
	// crutch for DatabaseRecords intermingling with ActiveRecords
	public function save()
	{
		// placeholder
	}
	
	
	
	// public methods
	public function createRecordDelta($newRecordFields)
	{
		return array_diff_assoc($newRecordFields, $this->_record);
	}
	
	// protected methods
	protected function modifyRecord($recordDelta)
	{
		// apply to database
		$set = array();
		
		foreach($recordDelta AS $column => $value)
		{
			$set[] = sprintf('`%s` = "%s"', $column, DB::escape($value));
		}
		
		if(count($set))
		{
			DB::nonQuery(
				'UPDATE `%s` SET %s WHERE `%s` = %u LIMIT 1'
				, array(
					$this->getConfig('TableName')
					, implode(',', $set)
					, $this->getConfig('IDColumn')
					, $this->ID
				)
			);
		}
		
		// apply to cached record
		$this->_record = array_merge($this->_record, $recordDelta);
		
		return (DB::affectedRows() == 1);
	}

	protected function deleteRecord()
	{
		DB::nonQuery(
			'DELETE FROM `%s` WHERE `%s` = %u LIMIT 1'
			, array(
				$this->getConfig('TableName')
				, $this->getConfig('IDColumn')
				, $this->ID
			)
		);
		
		if (DB::affectedRows() == 1)
		{
			$this->_record = null;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	static public function requireFields($requiredFields, $data, $className = __CLASS__)
	{
		foreach($requiredFields AS $fieldName)
		{
			if(!array_key_exists($fieldName, $data))
			{
				throw new RequiredFieldException($fieldName, $className, $data);
			}
		}
		
		return true;
	}
	
	
	// static methods	
	static public function create($recordFields = array(), $className = __CLASS__)
	{
		$tableName = call_user_func(array($className, 'getConfig'), 'TableName');
		
		$insertID = call_user_func(array($className, 'createRecord'), $tableName, $recordFields);
		
		return new $className($insertID);
	}
	
	static public function createRecord($tableName, $recordFields = array())
	{
		$set = array();
		
		foreach($recordFields AS $column => $value)
		{
			$set[] = sprintf('`%s` = "%s"', $column, DB::escape($value));
		}
		
		DB::nonQuery(
			'INSERT INTO `%s` %s'
			, array(
				$tableName
				, count($set) ? 'SET '.implode(',', $set) : ''
			)
		);

		return DB::insertID();		
	}
	
	
	static public function getConfig($key = false)
	{
		return $key ? self::$DefaultConfig[$key] : self::$DefaultConfig;
	}
	
	static public function getById($recordID, $className = __CLASS__)
	{
		$record = self::getRecordByID($recordID, $className);
		
		return $record ? new $className($record) : null;
	}
	
	static public function getRecordByID($recordID, $className = __CLASS__)
	{
		if ($cacheNamespace = call_user_func(array($className, 'getConfig'), 'CacheNamespace'))
		{
			$cacheKey = sprintf('%s/%u', $cacheNamespace, $recordID);
		}
		
		$query = 'SELECT * FROM %s WHERE %s = %u';
		$parameters = array(
			call_user_func(array($className, 'getConfig'), 'TableName')
			, call_user_func(array($className, 'getConfig'), 'IDColumn')
			, $recordID
		);
		
		// retrieve from ID number
		if (isset($cacheKey))
		{
			$record = DB::oneRecordCached($cacheKey, $query, $parameters);
		}
		else
		{
			$record = DB::oneRecord($query, $parameters);
		}
		
		return $record;
	}
	
	
	static public function getAll($className = __CLASS__)
	{
		return self::instantiateArray(
			DB::table(
				call_user_func(array($className, 'getConfig'), 'IDColumn')
				, 'SELECT * FROM %s'
				, call_user_func(array($className, 'getConfig'), 'TableName')
			)
			, $className
		);
	}
	
	
	static public function instantiateArrayKey($array, $keyName, $className = __CLASS__)
	{
		return array_map(
			create_function('$a', "\$a['$keyName'] = $className::getByID(\$a['$keyName']); return \$a;")
			, $array
		);
	}
	
	
	static public function instantiateArray($array, $className = __CLASS__)
	{
		return array_map(
			create_function('$a', "return new $className(\$a);")
			, $array
		);
	}

}