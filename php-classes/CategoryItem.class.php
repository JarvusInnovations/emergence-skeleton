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

	
	public function validate()
	{
		// call parent
		parent::validate();
		
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
		return static::delete($this->ContextClass,$this->ContextID,$this->CategoryID);
	}
	
	static public function delete($ContextClass,$ContextID,$CategoryID)
	{
		DB::nonQuery('DELETE FROM `%s` WHERE `%s` = \'%s\' AND `%s` = %u AND `%s` = %u', array(
			static::$tableName
			,static::_cn('ContextClass')
			,$ContextClass
			,static::_cn('ContextID')
			,$ContextID
			,static::_cn('CategoryID')
			,$CategoryID
		));
		
		return DB::affectedRows() > 0;
	}
	
	public function save()
	{
		global $Session;
		
		if($Session->Person)
		{
			$this->Creator = $Session->Person;
		}
		
		return parent::save(true);
	}
}