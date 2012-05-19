<?php


class TemplateResource extends Dwoo_Template_File
{

	public function getResourceName()
	{
		return 'Emergence';
	}
	
	public function __construct(SiteFile $templateNode)
	{
		return parent::__construct($templateNode->RealPath);
	}


	public static function templateFactory(Dwoo_Core $dwoo, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null, Dwoo_ITemplate $parentTemplate = null)
	{
		
		// return Dwoo_Template_File for absolute path
		if(substr($resourceId, 0, strlen(Site::$rootPath)) == Site::$rootPath)
		{
			//debug_print_backtrace();
			//die('foo');
			return new Dwoo_Template_File($file, $cacheTime, $cacheId, $compileId, $includePath);
		}
		
		// get current path
		$templatePath = Site::splitPath($resourceId);
		$localRoot = Site::getRootCollection('html-templates');
		$searchStack = array_filter(Site::$requestPath);
		$templateNode = false;
		
		while(true)
		{
			$searchPath = array_merge($searchStack, $templatePath);
			
			if($templateNode = $localRoot->resolvePath($searchPath))
			{
				break;
			}
			
			if($templateNode = Emergence::resolveFileFromParent('html-templates', $searchPath))
			{
				break;
			}
			
			// pop stack or quit search
			if(count($searchStack))
				array_pop($searchStack);
			else
				break;
		}
	
		if(!$templateNode)
		{
			throw new Exception('Could not getTemplate('.$resourceId.')');
		}


		return new static($templateNode);
	}

}