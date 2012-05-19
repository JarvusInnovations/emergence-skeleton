<?php

class CMS_BlogRequestHandler extends CMS_ContentRequestHandler
{
    // RecordsRequestHandler config
    static public $recordClass = 'CMS_BlogPost';
	static public $accountLevelAPI = 'User';
    static public $browseConditions = array(
    	'Class' => 'CMS_BlogPost'
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

	static public function checkWriteAccess(CMS_BlogPost $BlogPost)
	{
		// only allow creating, editing your own, and staff editing
		if(!$BlogPost->isPhantom && ($BlogPost->AuthorID != $GLOBALS['Session']->PersonID) && !$GLOBALS['Session']->hasAccountLevel('Staff'))
		{
			return false;
		}
		
		return true;
	}


}