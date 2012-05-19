<?php

class SiteDaemonsDavDirectory extends Sabre_DAV_FS_Directory
{
	private $directoryAlias;
	
	function __construct($directoryAlias)
	{
		$this->directoryAlias = $directoryAlias;
			
		parent::__construct($_SERVER['SITE_ROOT'].'/daemons');
	}
	
	function getName()
	{
		return $this->directoryAlias;
	}

	
	function getData()
	{
		return array(
			'Class' => 'SiteCollection'
			,'Handle' => $this->getName()
			,'FullPath' => $this->directoryAlias
		);
	}
}