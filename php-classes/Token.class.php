<?php

abstract class Token extends ActiveRecord
{
	static public $expirationHours = 48;
	static public $emailTemplate = 'token';

	static public $tableName = 'tokens';

	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__, 'PasswordToken');

	
	static public $fields = array(
		'Handle' => array(
			'type' => 'string'
			,'unique' => true
		)
		,'Expires' => array(
			'type' => 'timestamp'
			,'notnull' => false
		)
		,'Used' => array(
			'type' => 'timestamp'
			,'notnull' => false
		)
	);
	
	
	static public $relationships = array(
		'Creator' => array(
			'type' => 'one-one'
			,'class' => 'Person'
			,'local' => 'CreatorID'
		)
	);
	
	
	public function handleRequest($data)
	{
		// do nothing 
	}
	
	public function __get($name)
	{
		switch($name)
		{
			case 'isExpired':
			{
				return ($this->Expires < time());
			}
			
			case 'isUsed':
			{
				return $this->Used == true;
			}
			
			default: return parent::__get($name);
		}
	
	}

	public function save($deep = true)
	{
		// set handle
		if (!$this->Handle) {
    		$this->Handle = HandleBehavior::generateRandomHandle($this);
		}

		if(!$this->Expires)
		{
			$this->Expires = time() + (3600*static::$expirationHours);
		}

		// call parent
		parent::save($deep);
	}


	public function sendEmail($email)
	{
		return Emergence\Mailer\Mailer::sendFromTemplate($email, static::$emailTemplate, array(
    		'Token' => $this
		));
	}
}