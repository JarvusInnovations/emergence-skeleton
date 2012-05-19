<?php



 class SitePHPClassesDavDirectory extends MappedDavDirectory
{

    static public $mappedPath = '../classes';
    static public $directoryStatus = 'local';
    
    function __construct($directoryAlias)
    {
    	if(!is_dir(static::$mappedPath))
    	{
        	static::$mappedPath = '/var/mics/code/Library';
        	static::$directoryStatus = 'global';
        }
        
        parent::__construct($directoryAlias);
    }

}