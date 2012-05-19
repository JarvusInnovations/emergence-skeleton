<?php



 class LoginRequestHandler extends RequestHandler
{

	static public $defaultRedirect = '/';
	static public $forceRedirect = false;
	
	static public $onLoginComplete = false;
	static public $onLogoutComplete = false;

	// event templates
	static protected function onLoginComplete(Session $Session, $returnURL) {}
	static protected function onLogoutComplete(Session $Session, $returnURL) {}


	static public function handleRequest($returnURL = null)
	{
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
		
		if(static::peekPath() == 'logout')
		{
			return static::handleLogoutRequest($returnURL);
		}
		
		// force login
		$GLOBALS['Session']->requireAuthentication();
		
		$returnURL = static::getReturnURL($returnURL);
	
		if(is_callable(static::$onLoginComplete))
			call_user_func(static::$onLoginComplete, $GLOBALS['Session'], $returnURL);
			
		static::onLoginComplete($GLOBALS['Session'], $returnURL);
	
		// respond
		return static::respond('login/loginComplete', array(
			'success' => true
			,'data' => $GLOBALS['Session']
			,'returnURL' => $returnURL
		));
	}
	
	static public function handleLogoutRequest($returnURL = null)
	{
		// terminate session
		if (isset($GLOBALS['Session']))
		{
			$GLOBALS['Session']->terminate();
		}
				
		$returnURL = static::getReturnURL($returnURL);
	
		if(is_callable(static::$onLogoutComplete))
			call_user_func(static::$onLogoutComplete, $GLOBALS['Session'], $returnURL);
			
		static::onLogoutComplete($GLOBALS['Session'], $returnURL);

		// send redirect header
		// respond
		return static::respond('login/logoutComplete', array(
			'success' => true
			,'returnURL' => static::getReturnURL($returnURL)
		));
	}
	
	static protected function getReturnURL($returnURL = null)
	{
		if(static::$forceRedirect)
			return static::$forceRedirect;
		elseif($returnURL)
			return $returnURL;
		elseif(!empty($_REQUEST['returnURL']))
			return $_REQUEST['returnURL'];
		elseif(!empty($_REQUEST['return']))
			return $_REQUEST['return'];
		elseif(!empty($_SERVER['HTTP_REFERER']) && !preg_match('|^https?://[^/]+/login|i', $_SERVER['HTTP_REFERER']))
			return $_SERVER['HTTP_REFERER'];
		else
			return 'http://'.$_SERVER['HTTP_HOST'].static::$defaultRedirect;
	}


}