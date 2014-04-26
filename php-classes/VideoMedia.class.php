<?php

class VideoMedia extends Media
{

	// configurables
	public static $ExtractFrameCommand = 'ffmpeg -i %1$s -an -ss 00:00:%2$02u -vframes 1 -f mjpeg -'; // 1=flv path, 2=position
	public static $ExtractFramePosition = 3;
	
	
	// magic methods
	static public function __classLoaded()
	{
		$className = get_called_class();
		
		Media::$mimeHandlers['video/x-flv'] = $className;
		
		parent::__classLoaded();

	}
	
		
	function getValue($name)
	{
		switch($name)
		{
			case 'ThumbnailMIMEType':
				return 'image/jpeg';
				
			case 'Extension':

				switch($this->MIMEType)
				{
					case 'video/x-flv':
						return 'flv';
					default:
						throw new Exception('Unable to find video extension for mime-type: ' . $this->MIMEType);
				}
				
			default:
				return parent::getValue($name);
		}
	}
	
	
	// public methods
	public function getImage($sourceFile = null)
	{
		if (!isset($sourceFile))
		{
			$sourceFile = $this->FilesystemPath ? $this->FilesystemPath : $this->BlankPath;
		}

		$cmd = sprintf(self::$ExtractFrameCommand, $sourceFile, self::$ExtractFramePosition);
		
		return imagecreatefromstring(shell_exec($cmd));
	}
	
	// static methods
	static public function analyzeFile($filename, $mediaInfo = array())
	{
		$flvinfo = new FLVInfo();
		$mediaInfo['flvInfo'] = $flvinfo->getInfo($filename, true);
		
		$mediaInfo['width'] = $mediaInfo['flvInfo']->video->width;
		$mediaInfo['height'] = $mediaInfo['flvInfo']->video->height;
		$mediaInfo['duration'] = $mediaInfo['flvInfo']->duration;
		
	
		return $mediaInfo;
	}

}