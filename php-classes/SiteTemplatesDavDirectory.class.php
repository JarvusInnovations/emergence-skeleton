<?php



 class SiteTemplatesDavDirectory extends MappedDavDirectory
{

    static public $mappedPath = '../templates';
    static public $directoryStatus = 'live';
    
    function __construct($directoryAlias)
    {
    	if(static::$mappedPath != MICS::$TemplatePath)
    	{
        	static::$mappedPath = MICS::$TemplatePath;
        	static::$directoryStatus = 'dev';
        }
        
        parent::__construct($directoryAlias);
    }
    


}