<?php



 class UsersRequestHandler extends RecordsRequestHandler
{

	static public $visibleClasses;

	// configurables
	static public $recordClass = 'User';
	static public $accountLevelRead = 'User';
	static public $accountLevelBrowse = 'Staff';
	static public $accountLevelAPI = 'Staff';
	static public $browseOrder = array('ID' => 'DESC');
	static public $browseConditions = array('AccountLevel NOT IN ("Disabled","Person")');

	static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
	{
		if(!isset(static::$visibleClasses))
		{
			static::$visibleClasses = User::$subClasses;
		}
	
		$conditions[] = 'Class IN ("'.join('","', static::$visibleClasses).'")';
	
		return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
	}
	
	static public function handleRecordsRequest($action = false)
	{
		switch($action ? $action : $action = static::shiftPath())
		{
			case 'createUser':
				return static::handleCreateUserRequest();

			case 'editUser':
				return static::handleEditUserRequest();

			default:
			{
				return parent::handleRecordsRequest($action);
			}
		}
	}

	static public function handleRecordRequest(User $User, $action = false)
	{
		if(!static::checkReadAccess($User))
		{
			return static::throwUnauthorizedError();
		}

	
		switch($action ? $action : $action = static::shiftPath())
		{
			case '':
			case false:
			{
				PeopleRequestHandler::$responseMode = static::$responseMode;
				return PeopleRequestHandler::handlePersonRequest($User);
			}
			
			default:
			{
				return parent::handleRecordRequest($User, $action);
			}
		}
	}
	
	static public function handleEditUserRequest(ActiveRecord $Record = null) {
		
		$className = static::$recordClass;

		if(!static::checkWriteAccess($Record))
		{
			return static::throwUnauthorizedError();
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// apply delta
			static::applyRecordDelta($Record, $_REQUEST);
			
			if($Record->Password && strlen($Record->Password) != 40) {
				$Record->Password = call_user_func($className::$passwordHasher, $Record->Password);
			}
			
			// call template function
			static::onBeforeRecordValidated($Record, $_REQUEST);

			// validate
			if($Record->validate())
			{
				// call template function
				static::onBeforeRecordSaved($Record, $_REQUEST);
				
				// save session
				$Record->save();
				
				// call template function
				static::onRecordSaved($Record, $_REQUEST);
				
				// fire created response
				$responseID = static::getTemplateName($className::$singularNoun).'Saved';
				$responseData = static::getEditResponse($responseID, array(
					'success' => true
					,'data' => $Record
				));
				
				return static::respond($responseID, $responseData);
			}
			
			// fall through back to form if validation failed
		}
	
		$responseID = static::getTemplateName($className::$singularNoun).'Edit';
		$responseData = static::getEditResponse($responseID, array(
			'success' => false
			,'data' => $Record
		));
	
		return static::respond($responseID, $responseData);
	}
	
	static public function handleCreateUserRequest(ActiveRecord $Record = null) {
	
		// save static class
		static::$calledClass = get_called_class();

		if(!$Record)
		{
			$className = static::$recordClass;
			$Record = new $className::$defaultClass();
		}
		
		// call template function
		static::onRecordCreated($Record, $_REQUEST);
		
		return static::handleEditUserRequest($Record);
	}


}