<?php

abstract class VersionedRecord extends ActiveRecord
{
    // configure ActiveRecord
    static public $fields = array(
		'RevisionID' => array(
			'columnName' => 'RevisionID'
			,'type' => 'integer'
			,'unsigned' => true
			,'notnull' => false
		)
	);
	
	static public $relationships = array(
		'OldVersions' => array(
			'type' => 'history'
			,'order' => array('RevisionID' => 'DESC')
		)
	);
	


	// configure VersionedRecord
	static public $historyTable;


	/*
	 * Implement history relationship
	 */
	/*public function getValue($name)
	{
		switch($name)
		{
			case 'RevisionID':
			{
				return isset($this->_record['RevisionID']) ? $this->_record['RevisionID'] : null;
			}
			default:
			{
				return parent::getValue($name);
			}
		}
	}*/
	
	static protected function _initRelationship($relationship, $options)
	{
		if ($options['type'] == 'history') {
			if (empty($options['class'])) {
				$options['class'] = get_called_class();
			}
		}

		return parent::_initRelationship($relationship, $options);
	}

	protected function _getRelationshipValue($relationship)
	{
		if(!isset($this->_relatedObjects[$relationship]))
		{
			$rel = static::getStackedConfig('relationships', $relationship);

			if($rel['type'] == 'history')
			{
				$this->_relatedObjects[$relationship] = $rel['class']::getRevisionsByID($this->ID, $rel);
			}
		}
		
		return parent::_getRelationshipValue($relationship);
	}
	
	protected function _setFieldValue($field, $value)
	{
		// ignore setting versioning fields
		if(array_key_exists($field, self::$fields))
			return false;
		else
			return parent::_setFieldValue($field, $value);
	}	
	/*
	 * Implement specialized getters
	 */
	static public function getRevisionsByID($ID, $options = array())
	{
		$options['conditions']['ID'] = $ID;
		
		return static::getRevisions($options);
	}

	static public function getRevisions($options = array())
	{
		return static::instantiateRecords(static::getRevisionRecords($options));
	}
	
	static public function getRevisionRecords($options = array())
	{
		$options = array_merge(array(
			'indexField' => false
			,'conditions' => array()
			,'order' => false
			,'limit' => false
			,'offset' => 0
		), $options);
				
		$query = 'SELECT * FROM `%s` WHERE (%s)';
		$params = array(
			static::$historyTable
			, count($options['conditions']) ? join(') AND (', static::_mapConditions($options['conditions'])) : 1
		);
		
		if($options['order'])
		{
			$query .= ' ORDER BY ' . join(',', static::_mapFieldOrder($options['order']));
		}
		
		if($options['limit'])
		{
			$query .= sprintf(' LIMIT %u,%u', $options['offset'], $options['limit']);
		}
		
		
		if($options['indexField'])
		{
			return DB::table(static::_cn($options['indexField']), $query, $params);
		}
		else
		{
			return DB::allRecords($query, $params);
		}
	}
	
	
	/*
	 * Create new revisions on save
	 */
	public function save($deep = true)
	{
		$wasDirty = false;
		
		if($this->isDirty)
		{
			$wasDirty = true;
		}
	
		// save record as usual
		$return = parent::save($deep);

		if($wasDirty)
		{
			// save a copy to history table
			$recordValues = $this->_prepareRecordValues();
			
			$recordValues['Created'] = time();
			$recordValues['CreatorID'] = !empty($_SESSION) && !empty($_SESSION['User']) ? $_SESSION['User']->ID : null;
			
			$set = static::_mapValuesToSet($recordValues);
	
			DB::nonQuery(
				'INSERT INTO `%s` SET %s'
				, array(
					static::$historyTable
					, join(',', $set)
				)
			);
		}
		
	}

	public function getRootClass($boundingParentClass = __CLASS__)
	{
		return parent::getRootClass($boundingParentClass);
	}

	static public function getStaticRootClass($boundingParentClass = __CLASS__)
	{
		return parent::getStaticRootClass($boundingParentClass);
	}
	
}