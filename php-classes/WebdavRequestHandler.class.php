<?php

class WebdavRequestHandler extends RequestHandler
{

	static public function handleRequest()
	{
		// retrieve authentication attempt
		$authEngine = new Sabre_HTTP_BasicAuth();
		$authEngine->setRealm('Develop '.$_SERVER['HTTP_HOST']);
		$authUserPass = $authEngine->getUserPass();
		
		// try to get user
		$userClass = User::$defaultClass;
		$User = $userClass::getByLogin($authUserPass[0], $authUserPass[1]);
		
		// send auth request if login is inadiquate
		if(!$User || !$User->hasAccountLevel('Developer')) {
		    $authEngine->requireLogin();
		    echo "Authentication required\n";
		    die();
		}
		
		// store login to session
		$GLOBALS['Session'] = $GLOBALS['Session']->changeClass('UserSession', array(
		    'PersonID' => $User->ID
		));
		
		
        // handle /develop request
        if($_SERVER['REQUEST_METHOD'] == 'GET' && !static::peekPath())
        {
            RequestHandler::respond(!empty($_GET['classic'])?'Emergence/editor':'Emergence/develop');
        }
        
        
            
        if(static::peekPath() == 'json')
            static::$responseMode = static::shiftPath();
            
            
		// Change public to something else, if you are using a different directory for your files
		$rootDirectory = new SiteDavDirectory();
		
		// The server object is responsible for making sense out of the WebDAV protocol
		$server = new Sabre_DAV_Server($rootDirectory);
		
		// If your server is not on your webroot, make sure the following line has the correct information
		$server->setBaseUri('/' . Site::$requestPath[0] . (static::$responseMode=='json'?'/json':null));
		
		// $server->setBaseUri('/~evert/mydavfolder'); // if its in some kind of home directory
		// $server->setBaseUri('/dav/index.php/'); // if you can't use mod_rewrite, use index.php as a base uri
		// $server->setBaseUri('/'); // ideally, SabreDAV lives on a root directory with mod_rewrite sending every request to index.php
		
		// The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different 
		// directory, if you're storing your data somewhere else.
		$lockBackend = new Sabre_DAV_Locks_Backend_FS('/tmp/dav-lock');
		$lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
		$server->addPlugin($lockPlugin);
		
		// init browser plugin
		//if(!empty($_COOKIE['beta']))
			$browserPlugin = new DAVSiteEditorPlugin();
		//else
		//	$browserPlugin = new Sabre_DAV_Browser_Plugin();

		$server->addPlugin($browserPlugin);
		
		// filter temporary files
		$filterPlugin = new Sabre_DAV_TemporaryFileFilterPlugin('/tmp/dav-tmp');
		$server->addPlugin($filterPlugin);

		// ?mount support
		$server->addPlugin(new Sabre_DAV_Mount_Plugin());

		// emergence :)
		$server->addPlugin(new Emergence_Sabre_Dav_Plugin());
		
		// All we need to do now, is to fire up the server
		$server->exec();
	}
}