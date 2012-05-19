<?php


class ProfileRequestHandler extends RequestHandler
{

	static public $profileFields = array('Location','About','Phone','Email');

	static public function handleRequest()
	{
		// handle JSON requests
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
		
		// route request
		switch($action = strtolower(static::shiftPath()))
		{
			case 'uploadphoto':
			{
				return static::handlePhotoUploadRequest();
			}
		
			case 'primaryphoto':
			{
				return static::handlePhotoPrimaryRequest();
			}
		
			case 'deletephoto':
			{
				return static::handlePhotoDeleteRequest();
			}
		
			case 'password':
			{
				return static::handlePasswordRequest();
			}
			
			case 'view':
			{
				return static::handleViewRequest();
			}
		
			case '':
			case false:
			{
				return static::handleEditRequest();
			}
			
			default:
			{
				return static::throwNotFoundError();
			}
		
		}
		
	}
	
	static public function handleViewRequest()
	{
		$GLOBALS['Session']->requireAuthentication();

		return Router::redirectViewRecord($GLOBALS['Session']->Person);
	}
	
	static public function handleEditRequest()
	{
		$GLOBALS['Session']->requireAuthentication();
	
		$User = $GLOBALS['Session']->Person;
	
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$profileChanges = array_intersect_key($_REQUEST, array_flip(static::$profileFields));
		//Debug::dump($profileChanges, 'changes', true);
			$User->setFields($profileChanges);
			
			// validate
			if($User->validate())
			{
				// save session
				$User->save();
				
				// fire created response
				return static::respond('profileSaved', array(
					'success' => true
					,'data' => $User
				));
			}
			
			// fall through back to form if validation failed
		}
	
		return static::respond('profileEdit', array(
		));	
	}
	
	
	static public function handlePhotoUploadRequest()
	{
		$GLOBALS['Session']->requireAuthentication();

		// process photo upload with MediaRequestHandler
		MediaRequestHandler::$responseMode = 'return';
		$uploadResponse = MediaRequestHandler::handleUploadRequest(array(
			'fieldName' => 'photoFile'
			,'ContextClass' => 'Person'
			,'ContextID' => $_SESSION['User']->ID
			,'Caption' => $_SESSION['User']->FullName
		));
		
		// set primary if none set
		if(!$GLOBALS['Session']->Person->PrimaryPhoto)
		{
			$GLOBALS['Session']->Person->PrimaryPhotoID = $uploadResponse['data']['data']->ID;
			$GLOBALS['Session']->Person->save();
		}

		return static::respond('profilePhotoUploaded', $uploadResponse['data']);
	}
	
	static public function handlePhotoPrimaryRequest()
	{
		$GLOBALS['Session']->requireAuthentication();

		if(empty($_REQUEST['MediaID']) || !is_numeric($_REQUEST['MediaID']))
		{
			return static::throwInvalidRequestError();
		}
		
		if(!$Media = Media::getByID($_REQUEST['MediaID']))
		{
			return static::throwNotFoundError();
		}

		if($Media->ContextClass != 'Person' || $Media->ContextID != $GLOBALS['Session']->Person->ID)
		{
			return static::throwUnauthorizedError();
		}

		$GLOBALS['Session']->Person->PrimaryPhotoID = $Media->ID;
		$GLOBALS['Session']->Person->save();
		
		return static::respond('profilePhotoPrimaried', array(
			'success' => true
			,'data' => $Media
		));
	}


	static public function handlePhotoDeleteRequest()
	{
		$GLOBALS['Session']->requireAuthentication();

		if(empty($_REQUEST['MediaID']) || !is_numeric($_REQUEST['MediaID']))
		{
			return static::throwInvalidRequestError();
		}
		
		if(!$Media = Media::getByID($_REQUEST['MediaID']))
		{
			return static::throwNotFoundError();
		}

		if($Media->ContextClass != 'Person' || $Media->ContextID != $GLOBALS['Session']->Person->ID)
		{
			return static::throwUnauthorizedError();
		}

		$Media->destroy();
		
		return static::respond('profilePhotoDeleted', array(
			'success' => true
			,'data' => $Media
		));
	}
	
	
	static public function handlePasswordRequest()
	{
		$GLOBALS['Session']->requireAuthentication();
		
		if(empty($_REQUEST['OldPassword']))
		{
			return static::throwError('Enter your current password for verification');
		}
		elseif(call_user_func(User::$passwordHasher, $_REQUEST['OldPassword']) != $GLOBALS['Session']->Person->getPasswordHash())
		{
			return static::throwError('You did not enter your current password correctly');
		}
		elseif(empty($_REQUEST['Password']) || empty($_REQUEST['PasswordConfirm']))
		{
			return static::throwError('Enter your new password twice to change it');
		}
		elseif($_REQUEST['Password'] != $_REQUEST['PasswordConfirm'])
		{
			return static::throwError('The passwords you supplied did not match');
		}
	
		$GLOBALS['Session']->Person->Password = call_user_func(User::$passwordHasher, $_REQUEST['Password']);
		$GLOBALS['Session']->Person->save();
	
		return static::respond('passwordChanged', array(
			'success' => true
		));
	}
	
	

}