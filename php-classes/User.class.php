<?php


class User extends Person
{

	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);
	static public $singularNoun = 'user';
	static public $pluralNoun = 'users';
	
	// ActiveRecord configuration
	static public $fields = array(
		'Username' => array(
			'unique' => true
		)
		,'Password' => array(
			'type' => 'password'
			,'length' => 40
			,'hashFunction' => 'SHA1'
		)
		,'AccountLevel' => array(
			'type' => 'enum'
			,'values' => array('Disabled','Contact','User','Staff','Administrator','Developer')
			,'default' => 'User'
		)
	);

	// Person
	static public $requireEmail = true;

	// User
	static public $minPasswordLength = 5;
	static public $passwordHasher = 'SHA1';
	
	
	static function __classLoaded()
	{
		// merge User classes into valid Person classes, but not again when child classes are loaded
		if(get_called_class() == __CLASS__)
		{
			Person::$subClasses = static::$subClasses = array_merge(Person::$subClasses, static::$subClasses);
		}

		// finish ActiveRecord initialization
		parent::__classLoaded();
	}
	
	function getValue($name)
	{
		switch($name)
		{
			case 'AccountLevelNumeric':
			{
				return static::_getAccountLevelIndex($this->AccountLevel);
			}
			
			case 'Handle':
			{
				return $this->Username;
			}
			
			case 'SecretHashKey':
			{
				return SHA1($this->ID.$this->Username.$this->_record['Password']);
			}
		
			default: return parent::getValue($name);
		}
	
	}
	
	public function getData()
	{
		return array_merge(parent::getData(), array(
			'Password' => null
		));
	}

	
	
	public function validate()
	{
		// call parent
		parent::validate();
		
		$this->_validator->validate(array(
			'field' => 'Username'
			,'required' => true
			,'minlength' => 2
			,'maxlength' => 30
			,'errorMessage' => 'Username must be at least 2 characters'
		));
		
		
		$this->_validator->validate(array(
			'field' => 'Username'
			,'required' => true
			,'validator' => 'handle'
			,'errorMessage' => 'Username can only contain letters, numbers, hyphens, and underscores'
		));
		
		// check handle uniqueness
		if($this->isDirty && !$this->_validator->hasErrors('Username') && $this->Username)
		{
			$ExistingUser = User::getByUsername($this->Username);
			
			if($ExistingUser && ($ExistingUser->ID != $this->ID))
			{
				$this->_validator->addError('Username', 'Username already registered');
			}
		}

		$this->_validator->validate(array(
			'field' => 'Password'
			,'required' => true
			,'errorMessage' => 'Password required'
		));
				
				
		$this->_validator->validate(array(
			'field' => 'AccountLevel'
			,'validator' => 'selection'
			,'choices' => self::$fields['AccountLevel']['values']
			,'required' => false
		));
		
		// save results
		return $this->finishValidation();
	}
	
	public function save()
	{
		if(!$this->Username)
		{
			// auto generate username from first & last name
		}
		
		return parent::save();
	}
	
	static public function getByHandle($handle)
	{
		return static::getByUsername($handle);
	}
	
	// enable login by email
	static public function getByLogin($username, $password)
	{
		return static::getByWhere(array(
			sprintf('"%s" IN (`%s`, `%s`)', DB::escape($username), static::_cn('Username'), static::_cn('Email'))
			,'Password' => call_user_func(static::$passwordHasher, $password)
		));
	}
	
	static public function getByUsername($username)
	{
		return static::getByWhere(array(
			sprintf('"%s" IN (`%s`, `%s`)', DB::escape($username), static::_cn('Username'), static::_cn('Email'))
		));
	}
	public function matchPassword($password)
	{
		return call_user_func(static::$passwordHasher, $password) == $this->Password;
	}

	public function hasAccountLevel($accountLevel)
	{
		$accountLevelIndex = static::_getAccountLevelIndex($accountLevel);
		
		if($accountLevelIndex === false)
		{
			return false;
		}
		else
		{
			return ($this->AccountLevelNumeric >= $accountLevelIndex);
		}
	}
	
	public function getPasswordHash()
	{
		return $this->_record[static::_cn('Password')];
	}

	static public function getUniqueUsername($firstName, $lastName, $options = array())
	{
		// apply default options
		$options = Site::prepareOptions($options, array(
			'format' => 'short' // full or short
		));
		
		// create username
		switch($options['format'])
		{
			case 'short':
				$username = $firstName[0].$lastName;
				break;
			case 'full':
				$username = $firstName.'_'.$lastName;
				break;
			default:
				throw new Exception ('Unknown username format');
		}

		// strip bad characters
		$username = $strippedText = preg_replace(
			array('/\s+/', '/[^a-zA-Z0-9\-_]+/')
			, array('_', '-')
			, strtolower($username)
		);
				
		$incarnation = 1;
		while(static::getByWhere(array('Username'=>$username)))
		{
			// TODO: check for repeat posting here?
			$incarnation++;
			 
			$username = $strippedText . $incarnation;
		}
		
		return $username;
	}





	static protected function _getAccountLevelIndex($accountLevel)
	{
		return array_search($accountLevel, self::$fields['AccountLevel']['values']);
	}


}