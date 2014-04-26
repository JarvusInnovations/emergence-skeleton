<?php

class CMS_BlogRequestHandler extends CMS_ContentRequestHandler
{
    // RecordsRequestHandler config
    static public $recordClass = 'CMS_BlogPost';
	static public $accountLevelAPI = false;
	static public $accountLevelWrite = 'User';
    static public $browseConditions = array(
    	'Class' => 'CMS_BlogPost'
    	,'Status' => 'Published'
    );
    
    static public $browseLimitDefault = 25;
    

/*
	static protected function onRecordCreated(CMS_BlogPost $BlogPost)
	{
		parent::onRecordCreated($BlogPost);
		
		// initialite status
		$BlogPost->Status = 'Published';
	}

*/

	static public function handleRecordRequest(CMS_BlogPost $BlogPost, $action = false)
	{

		switch($action ? $action : $action = static::shiftPath())
		{
			
			case 'comment':
			{
				return CommentsRequestHandler::handleCreateRequest($BlogPost);
			}
	   
			default:
			{
				return parent::handleRecordRequest($BlogPost, $action);
			} 
		}
	}	
	
	static public function handleRequest()
	{
		if (!$GLOBALS['Session']->Person) {
			static::$browseConditions['Visibility'] = 'Public';
		}
		
		if (static::peekPath() == 'rss') {
			static::$responseMode = static::shiftPath();
		}
		
		if ($_REQUEST['AuthorID']) {
			static::$browseConditions['AuthorID'] = $_REQUEST['AuthorID'];
		}
		
		parent::handleRequest();
	}

	static public function checkWriteAccess(CMS_BlogPost $BlogPost)
	{
		// only allow creating, editing your own, and staff editing
		if (!$BlogPost->isPhantom && ($BlogPost->AuthorID != $GLOBALS['Session']->PersonID) && !$GLOBALS['Session']->hasAccountLevel('Staff')) {
			return false;
		}
		
		if ($BlogPost->isPhantom && !$GLOBALS['Session']->PersonID) {
			return false;
		}
		
		return true;
	}
	
	static public function respond($responseID, $data = array())
	{
		if (static::$responseMode == 'rss') {
			static::$responseMode = 'xml';
			
			if (static::$browseConditions['AuthorID']) {
				$User = User::getByID(static::$browseConditions['AuthorID']);
				$data['Author'] = $User;
				$data['Link'] = 'http://'.$_SERVER['HTTP_HOST'].'/people/' . $User->Username;
			}
			
			return parent::respond('rss', $data);
		} else {
			return parent::respond($responseID, $data);
		}
	}
	
	static public function checkReadAccess(CMS_BlogPost $BlogPost)
	{
		if ($BlogPost->Visibility == 'Private' && !$GLOBALS['Session']->Person) {
			return false;
		}
		
		return parent::checkReadAccess($BlogPost);
	}
}