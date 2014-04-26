<?php

class CommentsRequestHandler extends RecordsRequestHandler
{
    // RecordsRequestHandler configuration
	static public $recordClass = 'Comment';
	static public $accountLevelRead = false;
	static public $accountLevelBrowse = false;
	static public $accountLevelWrite = 'User';
	static public $browseOrder = array('ID' => 'DESC');
	
	static public $sendEmailNotifications = false;

	static public function handleRequest()
	{
	
		// handle JSON requests
		switch(static::peekPath())
		{
			case 'json':
			case 'rss':
			{
				static::$responseMode = static::shiftPath();
				break;
			}
		}
		
		
		
		// route request
		switch($commentID = static::shiftPath())
		{
			case 'create':
			{
				return static::handleCreateRequest();
			}

			case '':
			case false:
			{
				return static::handleBrowseRequest();
			}

			default:
			{
				// lookup comment by ID
				if(!$Comment = Comment::getByID($commentID))
				{
					return static::throwNotFoundError();
				}
				
				return static::handleCommentRequest($Comment);
			}
		}
	
	}
	
	
	
	static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
	{
		// accept conditions to limit results
		if(!empty($_REQUEST['q']))
		{
			$conditions[] = sprintf('MATCH (Message) AGAINST ("%s")', DB::escape($_REQUEST['q']));
		}
		
		if(!isset($options['limit']))
		{
			$options['limit'] = static::$responseMode == 'html' ? 15 : false;
		}
		
		return parent::handleBrowseRequest($options, $conditions);
	}
	
	static public function handleCreateRequest(ActiveRecord $Context = null)
	{
		// enable anonymous comment
		if($GLOBALS['Session']->hasAccountLevel(static::$accountLevelWrite))
		{
			$Comment = new Comment::$defaultClass();
		}
		elseif(in_array(Comment::$anonymousClass, Comment::$subClasses))
		{
			$Comment = new Comment::$anonymousClass();
			static::$accountLevelWrite = false;
		}
		
		
		if(!empty($Context))
		{
			$Comment->Context = $Context;
		}
		
		return static::handleEditRequest($Comment);
	}


	static public function handleCommentRequest(ActiveRecord $Comment)
	{
		switch($action = static::shiftPath())
		{
			case 'edit':
			{
				return static::handleEditRequest($Comment);
			}
			
			case 'delete':
			{
				return static::handleDeleteRequest($Comment);
			}
			
			case '':
			case false:
			{
				return static::respond('comment', array(
					'data' => $Comment
				));
			}
			
			default:
			{
				return static::throwNotFoundError();
			}
		}
	
	}
	
	
	static public function handleEditRequest(ActiveRecord $Comment)
	{
		$GLOBALS['Session']->requireAuthentication();
	
		if(!$Comment->isPhantom && !$Comment->userCanWrite)
		{
			return static::throwUnauthorizedError();
		}
	
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$Comment->Message = $_REQUEST['Message'];
			if(!empty($_REQUEST['ReplyToID']) && is_numeric($_REQUEST['ReplyToID']))
			{
				$Comment->ReplyToID = $_REQUEST['ReplyToID'];
			}
			
			// validate
			if($Comment->validate())
			{
				// save session
				$Comment->save();
				
				// fire created response
				return static::respond('commentSaved', array(
					'success' => true
					,'data' => $Comment
				));
			}
			
			// fall through back to form if validation failed
		}
	
		return static::respond('commentEdit', array(
			'success' => false
			,'data' => $Comment
		));
	}

	static public function handleDeleteRequest(ActiveRecord $Comment)
	{
		$GLOBALS['Session']->requireAuthentication();
	
		if(!$Comment->userCanWrite)
		{
			return static::throwUnauthorizedError();
		}
	
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$Comment->destroy();

			// fire created response
			return static::respond('commentDeleted', array(
				'success' => true
				,'data' => $Comment
			));
		}
	
		return static::respond('confirm', array(
			'question' => 'Are you sure you want to delete this comment?'
			,'data' => $Comment
		));
	}
	
	
	static public function respond($responseID, $responseData = array(), $responseMode = false)
	{
		if(static::$responseMode == 'rss')
		{
			static::$responseMode = 'xml';
			return parent::respond('rss', $responseData);
		}
		else
		{
			return parent::respond($responseID, $responseData);
		}
	}

	static protected function onRecordCreated(ActiveRecord $Record, $requestData)
	{
		if(static::$sendEmailNotifications)
		{
			$Comment->emailNotifications();
		}
	}
}