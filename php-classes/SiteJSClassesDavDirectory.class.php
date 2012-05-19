<?php



 class SiteJSClassesDavDirectory extends MappedDavDirectory
{

    static public $mappedPath = '/var/mics/code/JSLibrary';
    static public $directoryStatus = 'live';

    function __construct($directoryAlias)
    {
    	if(Debug::$DebugMode)
    	{
        	static::$mappedPath = '/var/mics/code/JSLibrary-dev';
        	static::$directoryStatus = 'dev';
        }
        
        parent::__construct($directoryAlias);
    }

}