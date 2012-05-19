<?php


class Media extends ActiveRecord
{

	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);

	// get rid of these??
	public static $Namespaces = array();
	public static $Types = array();


	static public $tableName = 'media';
	
	static public $fields = array(
		'ContextClass' => array(
			'type' => 'enum'
			,'values' => array('Person')
			,'notnull' => false
		)
		,'ContextID' => array(
			'type' => 'integer'
			,'notnull' => false
		)
		,'MIMEType' => array(
			'type' => 'enum'
			,'values' => array('image/gif','image/jpeg','image/png','video/x-flv','application/pdf')
		)
		,'Width' => array(
			'type' => 'integer'
			,'unsigned' => true
		)
		,'Height' => array(
			'type' => 'integer'
			,'unsigned' => true
		)
		,'Duration' => array(
			'type' => 'float'
		)
		,'Caption'
	);
	
	static public $relationships = array(
		'Creator' => array(
			'type' => 'one-one'
			,'class' => 'Person'
			,'local' => 'CreatorID'
		)
		,'Context' => array(
			'type' => 'context-parent'
		)
	);

	static public $webPathFormat = '/media/open/%u'; // 1=mediaID
	static public $thumbnailRequestFormat = '/thumbnail/%1$u/%2$ux%3$u%4$s'; // 1=media_id 2=width 3=height 4=fill_color
	static public $blankThumbnailRequestFormat = '/thumbnail/%1$s/%2$ux%3$u%4$s'; // 1=class 2=width 3=height 4=fill_color
	static public $thumbnailJPEGCompression = 90;
	static public $thumbnailPNGCompression = 9;
	static public $defaultFilenameFormat = 'default.%s.jpg';
	static public $newDirectoryPermissions = 0775;
	static public $newFilePermissions = 0664;
	static public $magicPath = null;//'/usr/share/misc/magic.mgc';
	
	static public $mimeHandlers = array();


	// privates
	protected $_webPath;
	protected $_filesystemPath;
	protected $_mediaInfo;

	static public function __classLoaded()
	{
		parent::__classLoaded();

		// load subclasses
		foreach(static::$subClasses AS $subClass)
		{
			Site::loadClass($subClass);
		}		
	}


	// magic methods
	function __get($name)
	{
		switch($name)
		{
			case 'Data':
			case 'SummaryData':
			case 'JsonTranslation':
				return array(
					'ID' => $this->ID
					,'Class' => $this->Class
					,'ContextClass' => $this->ContextClass
					,'ContextID' => $this->ContextID
					,'MIMEType' => $this->MIMEType
					,'Width' => $this->Width
					,'Height' => $this->Height
					,'Duration' => $this->Duration
				);
				
			case 'Filename':

				if ($this->ID)
				{
					return $this->ID . '.' . $this->Extension;
				}
				else
				{
					return 'default.' . $this->Extension;
				}
				
			case 'ThumbnailMIMEType':
				return $this->MIMEType;
				
			case 'Extension':
				throw new Exception('Unable to find photo extension for mime-type: ' . $this->MIMEType);
				
			case 'WebPath':

				if (!isset($this->_webPath))
				{
					$this->_webPath = sprintf(
						static::$webPathFormat
						, $this->ID
					);
				}
				
				return $this->_webPath;


			case 'FilesystemPath':

				if($this->ID == false)
				{
					return false;
				}

				return Site::$rootPath.'/media/original/'.$this->Filename;
				
				
			case 'BlankPath':
			
				return static::getBlankPath($this->ContextClass);


			default:
				return parent::__get($name);
		}
	}
	
	
	// public methods
	static public function getBlankThumbnailRequest($class, $width, $height, $fillColor = null)
	{
		return sprintf(
			static::$blankThumbnailRequestFormat
			, $class
			, $width
			, $height
			, ( isset($fillColor) ? 'x'.$fillColor : '' )
		);
	}
	
	public function getThumbnailRequest($width, $height, $fillColor = null)
	{
		return sprintf(
			static::$thumbnailRequestFormat
			, $this->ID
			, $width
			, $height
			, ( isset($fillColor) ? 'x'.$fillColor : '' )
		);
	}
	
	public function getConstrainedSize($maxWidth, $maxHeight)
	{
		// calculate scale ratios
		$widthRatio = ($this->Width > $maxWidth) ? ($maxWidth / $this->Width) : 1;
		$heightRatio = ($this->Height > $maxHeight) ? ($maxHeight / $this->Height) : 1;
		
		$ratio = min($widthRatio, $heightRatio);

		return array(
			'width' => round($this->Width * $ratio)
			, 'height' => round($this->Height * $ratio)
		);
	}
	
	public function getImage($sourceFile = null)
	{
		if (!isset($sourceFile))
		{
			$sourceFile = $this->FilesystemPath ? $this->FilesystemPath : $this->BlankPath;
		}
		
		if(!$fileData = @file_get_contents($sourceFile))
		{
			throw new Exception('Could not load media source: '.$sourceFile);
		}
		
		$fileImage = imagecreatefromstring($fileData);
				
		return $fileImage;
	}
	
	public function getThumbnail($maxWidth, $maxHeight, $fillColor = false)
	{
		// init thumbnail path
		$thumbFormat = sprintf('%ux%u', $maxWidth, $maxHeight);
		
		if ($fillColor)
		{
			$thumbFormat .= 'x'.strtoupper($fillColor);
		}
		
		$thumbPath = Site::$rootPath.'/media/'.$thumbFormat.'/'.$this->Filename;
				
		// look for cached thumbnail
		if (!file_exists($thumbPath))
		{
			
			// create new thumbnail
			$thumbnail = $this->createThumbnailImage($maxWidth, $maxHeight, $fillColor);
			
			//Debug::dump(imagesx($thumbnail),'thumb',true);
			
			// save thumbnail to cache
			$thumbDir = dirname($thumbPath);
			if (!is_dir($thumbDir))
			{
				mkdir($thumbDir, static::$newDirectoryPermissions, true);
			}
			
			switch($this->ThumbnailMIMEType)
			{
				case 'image/gif':
					imagegif($thumbnail, $thumbPath);
					break;
				
				case 'image/jpeg':
					imagejpeg($thumbnail, $thumbPath, static::$thumbnailJPEGCompression);
					break;
					
				case 'image/png':
					imagepng($thumbnail, $thumbPath, static::$thumbnailPNGCompression);
					break;
					
				default:
					throw new Exception('Unhandled thumbnail format');		
			}
			
			chmod($thumbPath, static::$newFilePermissions);		
		}
		
		
		// return path
		return $thumbPath;
	}
	
	
	public function createThumbnailImage($maxWidth, $maxHeight, $fillColor = false)
	{
		$scale = $this->getConstrainedSize($maxWidth, $maxHeight);

		// crop width/height to scale size if fill disabled
		if($fillColor)
		{
			$width = $maxWidth;
			$height = $maxHeight;
		}
		else
		{
			$width = $scale['width'];
			$height = $scale['height'];
		}
		
		// create images
		$srcImage = $this->getImage();
		$image = imagecreatetruecolor($width, $height);
		
		// paint fill color
		if ($fillColor)
		{
			// extract decimal values from hex triplet
			$fillColor = sscanf($fillColor, '%2x%2x%2x');

			// convert to color index
			$fillColor = imagecolorallocate($image, $fillColor[0], $fillColor[1], $fillColor[2]);
			
			// fill background
			imagefill($image, 0, 0, $fillColor);
		}
		elseif( ($this->MIMEType == 'image/gif') || ($this->MIMEType == 'image/png' ))
		{
			$trans_index = imagecolortransparent($srcImage);
			
			// check if there is a specific transparent color
			if($trans_index >= 0)
			{
				$trans_color = imagecolorsforindex($srcImage, $trans_index);
				
				// allocate in thumbnail
				$trans_index = imagecolorallocate($image, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
				
				// fill background
				imagefill($image, 0, 0, $trans_index);
				imagecolortransparent($image, $trans_index);

			}
			elseif($this->MIMEType == 'image/png' )
			{
				imagealphablending($image, false);
				$trans_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
				imagefill($image, 0, 0, $trans_color);
				imagesavealpha($image, true);
			}
			
/*
			$trans_index = imagecolorallocate($image, 218, 0, 245);
			ImageColorTransparent($image, $background); // make the new temp image all transparent
			imagealphablending($image, false); // turn off the alpha blending to keep the alpha channel
*/
		}
		
		
		//Debug::dump(imagesx($srcImage), 'srcImage');
		
		// resize photo to thumbnail
		imagecopyresampled(
			  $image
			, $srcImage
			, round( ($width - $scale['width']) / 2 ), round( ($height - $scale['height']) / 2 )
			, 0, 0
			, $scale['width'], $scale['height']
			, $this->Width, $this->Height
		);
		
		return $image;
	}
	
	/*
	public function delete()
	{
		// remove file
		@unlink($this->FilesystemPath);
		
		// delete record
		return $this->deleteRecord();
	}
	*/
	
	
	// static methods
	static public function createFromUpload($uploadedFile, $fieldValues = array())
	{
		// sanity check
		if(!is_uploaded_file($uploadedFile))
		{
			throw new Exception('Supplied file is not a valid upload');
		}
	
		return static::createFromFile($uploadedFile, $fieldValues);	
	}
		
	static public function createFromFile($file, $fieldValues = array())
	{
		// analyze file
		$mediaInfo = static::analyzeFile($file);
		
		// create media object
		$Media = $mediaInfo['className']::create($fieldValues);

		// init media
		$Media->initializeFromAnalysis($mediaInfo);
		
		// save media
		$Media->save();
		
		// move file
		$success = rename($file, $Media->FilesystemPath);
		if (!$success)
		{
			$error = error_get_last();

			// handle directory doesn't exist
			if (substr($error['message'], -25) == "No such file or directory")
			{
				mkdir(dirname($Media->FilesystemPath), static::$newDirectoryPermissions, true);
				
				$success = @rename($file, $Media->FilesystemPath);
			}
		}
		
		// set file permissions
		if ($success)
		{
			chmod($Media->FilesystemPath, static::$newFilePermissions);
			return $Media;
		}
		else
		{
			// remove photo record
			$Media->destroy();
			
			throw new Exception('Media import failed');
		}
	}
	
	public function initializeFromAnalysis($mediaInfo)
	{
		global $Session;
	
		$this->MIMEType = $mediaInfo['mimeType'];
		$this->Width = $mediaInfo['width'];
		$this->Height = $mediaInfo['height'];
		$this->Duration = $mediaInfo['duration'];
	}
	
	
	static public function analyzeFile($filename)
	{
		// DO NOT CALL FROM decendent's override, parent calls child
	
		$mediaInfo = array();
		
		
		// check file
		if(!is_readable($filename))
		{
			throw new Exception('Unable to read media file for analysis: "'.$filename.'"');
		}
		
		// get mime type
		$finfo = finfo_open(FILEINFO_MIME, static::$magicPath);
		
		if(!$finfo || !($mimeInfo = finfo_file($finfo, $filename)) )
		{
			throw new Exception('Unable to load media file info');
		}

		finfo_close($finfo);

		// split mime type
		$p = strpos($mimeInfo, ';');
		$mediaInfo['mimeType'] = $p ? substr($mimeInfo, 0, $p) : $mimeInfo;
		
		// determine type
		if(!isset(static::$mimeHandlers[$mediaInfo['mimeType']]))
		{
			//Debug::dump(static::$mimeHandlers, 'MIME Handlers');
			throw new MediaTypeException('No class registered for mime type "' . $mediaInfo['mimeType'] . '"');
		}
		
		$mediaInfo['className'] = static::$mimeHandlers[$mediaInfo['mimeType']];
		
		// call registered type's analyzer
		$mediaInfo = call_user_func(array($mediaInfo['className'], 'analyzeFile'), $filename, $mediaInfo);
		
		return $mediaInfo;
	}
	
	static public function getBlankPath($contextClass)
	{
		return Site::$rootPath.'/media/original/'.sprintf(static::$defaultFilenameFormat, $contextClass);
	}
	
	static public function getBlank($contextClass)
	{
		// get image info
		$sourcePath = static::getBlankPath($contextClass);
		$sourceInfo = @getimagesize($sourcePath);
		
		if (!$sourceInfo)
		{
			throw new Exception("Unable to load blank image for context '$contextClass' from '$sourcePath'");
		}
		
		// get mime type
		$mimeType = image_type_to_mime_type($sourceInfo[2]);
		
		// determine type
		if(!isset(static::$mimeHandlers[$mimeType]))
		{
			throw new MediaTypeException('No class registered for mime type "' . $mimeType . '"');
		}
		
		$className = static::$mimeHandlers[$mimeType];
		
		
		$blankMedia = new $className();
		$blankMedia->ContextClass = $contextClass;
		$blankMedia->MIMEType = $mimeType;
		$blankMedia->Width = $sourceInfo[0];
		$blankMedia->Height = $sourceInfo[1];
		
		return $blankMedia;
		
	}




}