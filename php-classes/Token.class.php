<?php

abstract class Token extends ActiveRecord
{

	static public $expirationHours = 48;
	static public $emailTemplate = 'token.email';
	static public $emailSubject = 'The link you requested';
	static public $emailFrom;

	static public $tableName = 'tokens';

	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__, 'PasswordToken');

	
	static public $fields = array(
		'ContextClass' => null
		,'ContextID' => null
		,'Handle' => array(
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
		
	static public function getByHandle($handle)
	{
		return static::getByField('Handle', $handle, true);
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

	public function save()
	{
		// set handle
		if(!$this->Handle)
		{
			$this->Handle = static::generateUniqueHandle();
		}

		if(!$this->Expires)
		{
			$this->Expires = time() + (3600*static::$expirationHours);
		}

		// call parent
		parent::save();
	}


	public static function generateUniqueHandle()
	{
		do
		{
			$handle = md5(mt_rand(0, mt_getrandmax()));
		}
		while( static::getByHandle($handle) );
		
		return $handle;
	}


	public function sendEmail($email)
	{
		$body = TemplateResponse::getSource(static::$emailTemplate, array(
			'Token' => $this
		));
										
		return Email::send($email, static::$emailSubject, $body, static::$emailFrom);
	}

}