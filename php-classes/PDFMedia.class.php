<?php



 class PDFMedia extends Media
{

	// configurables
	public static $extractPageCommand = 'convert \'%1$s[%2$u]\' JPEG:- 2>&1'; // 1=pdf path, 2=page
	public static $extractPageIndex = 0;
	
	
	// magic methods
	static public function __classLoaded()
	{
		$className = get_called_class();
		
		Media::$mimeHandlers['application/pdf'] = $className;
		
		parent::__classLoaded();
	}
	
	
	function __get($name)
	{
		switch($name)
		{
			case 'JsonTranslation':
				return array_merge(parent::__get($name), array(
				));
			
			case 'ThumbnailMIMEType':
				return 'image/jpeg';
				
			case 'Extension':

				switch($this->MIMEType)
				{
					case 'application/pdf':
						return 'pdf';
					default:
						throw new Exception('Unable to find document extension for mime-type: ' . $this->MIMEType);
				}
				
			default:
				return parent::__get($name);
		}
	}
	
	
	// public methods
	public function getImage($sourceFile = null)
	{
		if (!isset($sourceFile))
		{
			$sourceFile = $this->FilesystemPath ? $this->FilesystemPath : $this->BlankPath;
		}

		$cmd = sprintf(static::$extractPageCommand, $sourceFile, static::$extractPageIndex);
		
		$fileData = shell_exec($cmd);
		$fileImage = imagecreatefromstring($fileData);
		
		return $fileImage;
	}
	
	// static methods
	static public function analyzeFile($filename, $mediaInfo = array())
	{
		$cmd = sprintf(static::$extractPageCommand, $filename, static::$extractPageIndex);
		$pageIm = imagecreatefromstring(shell_exec($cmd));
		
		$mediaInfo['width'] = imagesx($pageIm);
		$mediaInfo['height'] = imagesy($pageIm);
	
		return $mediaInfo;
	}
	

}