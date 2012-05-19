<?php



 class ForumsRequestHandler extends RequestHandler
{


	static public function handleRequest()
	{
		// handle JSON requests
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
		
		// route request
		switch($forumHandle = static::shiftPath())
		{
			case '':
			case false:
			{
				return static::handleBrowseRequest();
			}
			
			default:
			{
				// lookup forum by handle
				if(!$Forum = Forum::getByHandle($forumHandle))
				{
					return static::throwNotFoundError();
				}
				
				return static::handleForumRequest($Forum);
			}
		}
	
	}
	
	
	
	static public function handleBrowseRequest()
	{
		// execute search and return response
		return static::respond('forums', array(
			'data' => Forum::getAllAccessible()
		));
	}


	static public function handleForumRequest(Forum $Forum)
	{
		switch($action = static::shiftPath())
		{
			case 'create':
			{
				return static::handleDiscussionCreateRequest($Forum);
			}
			
			case '':
			case false:
			{
				return static::respond('forum', array(
					'data' => $Forum
				));
			}
			
			default:
			{
				return static::throwNotFoundError();
			}
		}
	
	}
	
	
	static public function handleDiscussionCreateRequest(Forum $Forum)
	{
		$GLOBALS['Session']->requireAuthentication();
	
		$Discussion = ForumDiscussion::create(array(
			'ContextClass' => 'Forum'
			,'ContextID' => $Forum->ID
		));
	
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$Discussion->setFields($_REQUEST);

			// validate
			if($Discussion->validate())
			{
				// save session
				$Discussion->save();
				
				// save opening comment
				if(!empty($_REQUEST['Comment']))
				{
					Comment::create(array(
						'ContextClass' => 'Discussion'
						,'ContextID' => $Discussion->ID
						,'Message' => $_REQUEST['Comment']
					), true);
				}
				
				// fire created response
				return static::respond('discussionCreated', array(
					'success' => true
					,'data' => $Discussion
				));
			}
			
			// fall through back to form if validation failed
		}
	
		return static::respond('discussionCreate', array(
			'success' => false
			,'data' => $Discussion
		));
	}
	

	

}