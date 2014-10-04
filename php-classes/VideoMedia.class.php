<?php

class VideoMedia extends Media
{

    // configurables
    public static $ExtractFrameCommand = 'avconv -i %1$s -an -ss 00:00:%2$02u -vframes 1 -f mjpeg -'; // 1=flv path, 2=position
	public static $ExtractFramePosition = 3;
	
	
	// magic methods
	static public function __classLoaded()
	{
		$className = get_called_class();
		
		Media::$mimeHandlers['video/x-flv'] = $className;
		Media::$mimeHandlers['video/mp4'] = $className;
		
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
						
					case 'video/mp4':
						return 'mp4';
							
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
		if (!isset($sourceFile)) {
			$sourceFile = $this->FilesystemPath ? $this->FilesystemPath : $this->BlankPath;
		}

		$cmd = sprintf(self::$ExtractFrameCommand, $sourceFile, self::$ExtractFramePosition);

		if ($imageData = shell_exec($cmd)) {
			return imagecreatefromstring($imageData);
		} elseif($sourceFile != $this->BlankPath) {
			return static::getImage($this->BlankPath);
		}

		return null;
	}
	
	// static methods
	static public function analyzeFile($filename, $mediaInfo = array())
	{
		switch($mediaInfo['mimeType'])
		{
			case 'video/x-flv':
			{	
				$flvinfo = new FLVInfo();
				$mediaInfo['info'] = $flvinfo->getInfo($filename, true);	
				$mediaInfo['flvInfo'] = $mediaInfo['info'];
				
				break;
				
			}
			
			case 'video/mp4':
			{
				$mp4info = new MP4Info();
				$mediaInfo['info'] = $mp4info->getInfo($filename, true);	
				$mediaInfo['mp4Info'] = $mediaInfo['info'];
				
				break;
			}	
		
		}

		
		$mediaInfo['width'] = $mediaInfo['info']->video->width;
		$mediaInfo['height'] = $mediaInfo['info']->video->height;
		$mediaInfo['duration'] = $mediaInfo['info']->duration;
		$mediaInfo['audioCodec'] = $mediaInfo['info']->audio->codecStr;
		$mediaInfo['videoCodec'] = $mediaInfo['info']->video->codecStr;
	
		return $mediaInfo;
	}

}