<?php



 class MappedDavDirectory extends Sabre_DAV_FS_Directory
{

	static public $mappedPath;
	static public $directoryStatus;

	private $directoryAlias;
	
	function __construct($directoryAlias)
	{
		$this->directoryAlias = $directoryAlias;
		
		if(isset(static::$directoryStatus))
			$this->directoryAlias .= ' ('.static::$directoryStatus.')';
	
		parent::__construct(static::$mappedPath);
	}
	
	function getName()
	{
		return $this->directoryAlias;
	}

}