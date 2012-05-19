<?php



class PeopleRequestHandler extends RecordsRequestHandler
{

	// RecordRequestHandler configuration
	static public $recordClass = 'Person';

	static public $searchConditions = array(
		'FirstName' => array(
			'qualifiers' => array('any','name','fname','firstname','first')
			,'points' => 2
			,'sql' => 'FirstName LIKE "%%%s%%"'
		)
		,'LastName' => array(
			'qualifiers' => array('any','name','lname','lastname','last')
			,'points' => 2
			,'sql' => 'LastName LIKE "%%%s%%"'
		)
		,'Username' => array(
			'qualifiers' => array('any','username','uname','user')
			,'points' => 2
			,'sql' => 'Username LIKE "%%%s%%"'
		)
		/*,'Email' => array(
			'qualifiers' => array('any','email','mail')
			,'points' => 2
			,'sql' => 'Email LIKE "%%%s%%"'
		)*/
	);

	static public function getRecordByHandle($handle)
	{
		if(ctype_digit($handle))
			return Person::getByID($handle);
		else
			return User::getByUsername($handle);
	}
	
	static public function respond($responseID, $responseData = array(), $responseMode = false)
	{
		$responseData['enums'] = array(
			'Class' => Person::$subClasses
			,'AccountLevel' => User::$fields['AccountLevel']['values']
		);
		
		$responseData['defaults'] = array(
			'Class' => Person::$defaultClass
			,'AccountLevel' => User::$fields['AccountLevel']['default']
		);
	
		return parent::respond($responseID, $responseData);
	}
	
	static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
	{
		$GLOBALS['Session']->requireAccountLevel('Staff');
			
		if($_REQUEST['q'] != 'all')
		{
			$conditions[] = 'AccountLevel != "Disabled"';
		}
		
		return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
	}
	
}