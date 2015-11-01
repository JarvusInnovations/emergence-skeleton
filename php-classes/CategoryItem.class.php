<?php

class CategoryItem extends ActiveRecord
{

	static public $tableName = 'category_items';
	static public $rootClass = __CLASS__;
	
	static public $fields = array(
		'ID' => null
		,'Class' => null
		,'ContextClass' => array(
			'type' => 'string'
			,'notnull' => false
		)
		,'ContextID' => array(
			'type' => 'integer'
			,'notnull' => false
		)
		,'CategoryID' => array(
			'type' => 'integer'
			,'index' => true
		)
	);
	
	static public $relationships = array(
		'Category' => array(
			'type' => 'one-one'
			,'class' => 'Category'
		)
	);

	static public $indexes = array(
		'CategoryItem' => array(
			'fields' => array('CategoryID','ContextClass','ContextID')
			,'unique' => true
		)
	);

	
	public function validate($deep = true)
	{
		// call parent
		parent::validate($deep);
		
		$this->_validator->validate(array(
			'field' => 'CategoryID'
			,'validator' => 'number'
		));
		
		$this->_validator->validate(array(
			'field' => 'ContextClass'
			,'validator' => 'className'
		));
		
		$this->_validator->validate(array(
			'field' => 'ContextID'
			,'validator' => 'number'
		));
				
		// save results
		$this->_isValid = $this->_isValid && !$this->_validator->hasErrors();
		if(!$this->_isValid)
		{
			$this->_validationErrors = array_merge($this->_validationErrors, $this->_validator->getErrors());	
		}


		return $this->_isValid;
	}

    public function destroy()
	{
    	DB::nonQuery('DELETE FROM `%s` WHERE ContextClass = "%s" AND ContextID = %u AND CategoryID = %u', array(
			static::$tableName,
			DB::escape($ContextClass),
			$ContextID,
			$CategoryID
		));
		
		return DB::affectedRows() > 0;
	}
	
	static public function delete($id)
	{
		throw new \Exception('Static destruction not supported');
	}
}