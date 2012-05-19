<?php


class EmailSubscriber extends ActiveRecord
{
	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);

	// ActiveRecord configuration
	static public $tableName = 'email_subscribers';
	static public $singularNoun = 'email_subscriber';
	static public $pluralNoun = 'email subscribers';

	static public $fields = array(
		'ContextClass' => null
		,'ContextID' => null
		,'Name' => array(
			'type' => 'string'
			,'notnull' => false
		)
		,'Email' => array(
			'type' => 'string'
			,'unique' => true
			,'notnull' => true
		)
	);
	
	public function validate()
	{
		// call parent
		parent::validate();

		$this->_validator->validate(array(
			'field' => 'Email'
			,'validator' => 'email'
		));
        
        

		// save results
		return $this->finishValidation();
	}
}