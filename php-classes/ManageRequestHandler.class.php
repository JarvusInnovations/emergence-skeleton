<?php



 class ManageRequestHandler extends RequestHandler
{

	static public $viewportLoader = 'Manager.Viewport';

	static public $applicationPanels = array(
		//'dashboard' => 'Dashboard.DashboardPanel'
		'browser' => 'Browser.BrowserPanel'
		,'people' => 'People.PeopleManager'
		,'media' => 'Media.MediaManager'
	);
	
	static public $contextPanels = array(
		'Person' => array(
			'People.PersonDetailsPanel'
			,'People.PersonGroupsPanel'
			,'People.PersonJournalPanel'
		)
	);
	
	static public $globalUse = array();


	static public function handleRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Staff');
	
		// handle JSON requests
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
		
		// route request
		switch($request = static::shiftPath())
		{
			case 'sandbox':
			{
				return static::handleSandboxRequest();
			}
		
			default:
			{
				return static::handleConsoleRequest();
			}
		}
	
	}
	
	static public function handleConsoleRequest()
	{
		return static::respond('console', array(
			'success' => true
			,'viewportLoader' => static::$viewportLoader
			,'applicationPanels' => static::$applicationPanels
			,'contextPanels' => static::$contextPanels
		));
	}
	
	static public function handleSandboxRequest()
	{
		return static::respond('sandbox', array(
			'success' => true
		));
	}
	

}