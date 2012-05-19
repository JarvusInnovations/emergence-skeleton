<?php


class ExtLibraryRequestHandler extends RequestHandler
{
	static public $libraryCollection = 'ext-library';
	static public $cacheMaxAge = 1800;

	static public function handleRequest()
	{
		// build file path
		$filePath = Site::$pathStack;
		array_unshift($filePath, static::$libraryCollection);
		
		// try to get node
		$fileNode = Site::resolvePath($filePath);
		
		if($fileNode)
			$fileNode->outputAsResponse();
		else
			Site::respondNotFound('Resource not found');
	}


}
