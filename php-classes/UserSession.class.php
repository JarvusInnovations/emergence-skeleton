<?php



 class UserSession extends Session
{

	// ActiveRecord configuration
	static public $subClasses = array('Session','UserSession');

	static public $fields = array(
		'PersonID' => array(
			'type' => 'integer'
			,'unsigned' => true
		)
	);
	
	static public $relationships = array(
		'Person' => array(
			'type' => 'one-one'
			,'class' => 'Person'
			,'local' => 'PersonID'
		)
	);
    
    public static $dynamicFields = array(
        'Person'
    );
	

	// UserSession
	static public $requireAuthentication = false;
	static public $defaultAuthenticator = 'PasswordAuthenticator';
	public $authenticator;
	
	function __construct($record = array())
	{
		parent::__construct($record);
		
		if(!isset($this->authenticator))
		{
			$this->authenticator = new static::$defaultAuthenticator($this);
		}
		
		// check authentication
		$this->authenticator->checkAuthentication();
		
		// require authentication ?
		if(static::$requireAuthentication)
		{
			if( !$this->requireAuthentication() )
			{
				throw new AuthenticationFailedException();
			}
		}
		
		// export data to _SESSION superglobal
		$_SESSION['User'] = $this->Person ? $this->Person : false;
	}
	
	public function requireAuthentication()
	{
		return $this->authenticator->requireAuthentication();
	}
	
	public function requireAccountLevel($accountLevel)
	{
		$this->requireAuthentication();

		if( !is_a($this->Person, 'User') || !$this->Person->hasAccountLevel($accountLevel) )
		{
			ErrorHandler::handleInadaquateAccess($accountLevel);
			exit();
		}
	}
	
	public function hasAccountLevel($accountLevel)
	{
		return $this->Person && $this->Person->hasAccountLevel($accountLevel);
	}

}