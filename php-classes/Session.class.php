<?php

class Session extends ActiveRecord
{

	// Session configurables
	static public $cookieName = 's';
	static public $cookieDomain = null;
	static public $cookiePath = '/';
	static public $cookieSecure = false;
	static public $cookieExpires = false;
	static public $timeout = 3600;

	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);

	// ActiveRecord configuration
	static public $tableName = 'sessions';
	static public $singularNoun = 'session';
	static public $pluralNoun = 'sessions';
	
	static public $fields = array(
		'ContextClass' => null
		,'ContextID' => null
		,'Handle' => array(
			'unique' => true
		)
		,'LastRequest' => array(
			'type' => 'timestamp'
		)
		,'LastIP' => array(
			'type' => 'integer'
			,'unsigned' => true
		)
		,'CreatorID' => null
	);
	
	
	// Session
	static function __classLoaded()
	{
		parent::__classLoaded();
	
		// auto-detect cookie domain
		if(empty(static::$cookieDomain))
		{
			static::$cookieDomain = preg_replace('/^www\.([^.]+\.[^.]+)$/i', '$1', $_SERVER['HTTP_HOST']);
		}
	}
	
	
	static public function getFromRequest($create = true)
	{
		$sessionData = array(
			'LastIP' => ip2long($_SERVER['REMOTE_ADDR'])
			,'LastRequest' => time()
		);
	
		// try to load from cookie
		if(!empty($_COOKIE[static::$cookieName]))
		{
			if($Session = static::getByHandle($_COOKIE[static::$cookieName]))
			{
				// update session & check expiration
				$Session = static::updateSession($Session, $sessionData);
			}
		}
		
		// try to load from any request method
		if(empty($Session) && !empty($_REQUEST[static::$cookieName]))
		{
			if($Session = static::getByHandle($_REQUEST[static::$cookieName]))
			{
				// update session & check expiration
				$Session = static::updateSession($Session, $sessionData);
			}
		}
		
		if(!empty($Session))
		{
			// session found
			return $Session;
		}
		elseif($create)
		{
			// create session
			return static::create($sessionData, true);
		}
		else
		{
			// no session available
			return false;
		}
	}
	
	static public function updateSession(Session $Session, $sessionData)
	{

		// check timestamp
		if(static::$timeout && $Session->LastRequest < (time() - static::$timeout))
		{
			$Session->terminate();
			
			return false;
		}
		else
		{
			// update session
			$Session->setFields($sessionData);
			$Session->save();
			
			return $Session;
		}
	}
	
	static public function getByHandle($handle)
	{
		return static::getByField('Handle', $handle, true);
	}
	
	public function getData()
	{
		// embed related object(s)
		return array_merge(parent::getData(), array(
			'Person' => $this->Person ? $this->Person->getData() : null
		));
	}

	public function save($deep = true)
	{
		// set handle
		if (!$this->Handle) {
			$this->Handle = HandleBehavior::generateRandomHandle($this);
		}

		// call parent
		parent::save($deep);
		
		// set cookie
		setcookie(
			static::$cookieName
			, $this->Handle
			, static::$cookieExpires ? (time() + static::$cookieExpires) : 0
			, static::$cookiePath
			, static::$cookieDomain
			, static::$cookieSecure
		);
	}
	
	public function terminate()
	{
		setcookie(static::$cookieName, '', time() - 3600);
		unset($_COOKIE[static::$cookieName]);
		
		$this->destroy();
	}
}