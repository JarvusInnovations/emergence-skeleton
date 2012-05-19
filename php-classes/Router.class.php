<?php



 class Router
{

	static public $classPaths = array(
		'Person' => 'users'
		,'Media' => 'media'
		,'Event' => 'events'
		,'Page' => 'pages'
		,'CMS_Content' => 'content'
		,'CMS_Page' => 'pages'
		,'CMS_BlogPost' => 'blog'
		,'CMS_Feature' => 'features'
	);
	
	static public function getClassPath($className)
	{
		if(is_object($className))
			$className = get_class($className);
			
		if(!empty(static::$classPaths[$className]))
			return static::$classPaths[$className];
			
		foreach(class_parents($className) AS $parentName)
		{
			if(!empty(static::$classPaths[$parentName]))
				return static::$classPaths[$parentName];
		}
	
		return false;
	}
	
	static public function redirectViewRecord(ActiveRecord $Record, $path = array())
	{
		if(!$classPath = static::getClassPath($Record))
		{
			return RequestHandler::throwNotFoundError('No route to record viewer');
		}
		
		// prepend record handle
		array_unshift($path, $Record->Handle);
		
		// prepend record handler
		array_unshift($path, $classPath);
		
		Site::redirect($path);
	}

}