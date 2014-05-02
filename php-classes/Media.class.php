<?php

class Media extends ActiveRecord
{
    static public $singularNoun = 'media item';
    static public $pluralNoun = 'media items';

    // support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);
    static public $collectionRoute = '/media';

	// get rid of these??
	public static $Namespaces = array();
	public static $Types = array();


	static public $tableName = 'media';
	
	static public $fields = array(
		'ContextClass' => array(
			'type' => 'string'
			,'notnull' => false
		)
		,'ContextID' => array(
			'type' => 'integer'
			,'notnull' => false
		)
		,'MIMEType' => array(
			'type' => 'enum'
			,'values' => array('image/gif','image/jpeg','image/png','video/x-flv','application/pdf', 'video/mp4')
		)
		,'Width' => array(
			'type' => 'integer'
			,'unsigned' => true
        	,'notnull' => false
		)
		,'Height' => array(
			'type' => 'integer'
			,'unsigned' => true
        	,'notnull' => false
		)
		,'Duration' => array(
			'type' => 'float'
    		,'unsigned' => true
    		,'notnull' => false
		)
		,'Caption' => array(
            'type' => 'string'
    		,'notnull' => false
        )
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
	
	static public $searchConditions = array(
		'Caption' => array(
			'qualifiers' => array('any','caption')
			,'points' => 2
			,'sql' => 'Caption LIKE "%%%s%%"'
		)
		,'CaptionLike' => array(
			'qualifiers' => array('caption-like')
			,'points' => 2
			,'sql' => 'Caption LIKE "%s"'
		)
		,'CaptionNot' => array(
			'qualifiers' => array('caption-not')
			,'points' => 2
			,'sql' => 'Caption NOT LIKE "%%%s%%"'
		)
		,'CaptionNotLike' => array(
			'qualifiers' => array('caption-not-like')
			,'points' => 2
			,'sql' => 'Caption NOT LIKE "%s"'
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
    static public $useFaceDetection = true;
	
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
	function getValue($name)
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

				return Site::$rootPath.'/site-data/media/original/'.$this->Filename;
				
				
			case 'BlankPath':
			
				return static::getBlankPath($this->ContextClass);


			default:
				return parent::getValue($name);
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
	
	public function getThumbnailRequest($width, $height, $fillColor = null, $cropped = false)
	{
		return sprintf(
			static::$thumbnailRequestFormat
			, $this->ID
			, $width
			, $height
			, ( is_string($fillColor) ? 'x'.$fillColor : '' )
		) . ($cropped ? '/cropped' : '');
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
	
	public function getThumbnail($maxWidth, $maxHeight, $fillColor = false, $cropped = false)
	{
		// init thumbnail path
		$thumbFormat = sprintf('%ux%u', $maxWidth, $maxHeight);
		
		if ($fillColor)
		{
			$thumbFormat .= 'x'.strtoupper($fillColor);
		}
        
        if ($cropped) {
            $thumbFormat .= '.cropped';
        }
		
		$thumbPath = Site::$rootPath.'/site-data/media/'.$thumbFormat.'/'.$this->Filename;
				
		// look for cached thumbnail
		if (!file_exists($thumbPath)) {
            // ensure directory exists
    		$thumbDir = dirname($thumbPath);
			if (!is_dir($thumbDir)) {
				mkdir($thumbDir, static::$newDirectoryPermissions, true);
			}
            
			// create new thumbnail
			$this->createThumbnailImage($thumbPath, $maxWidth, $maxHeight, $fillColor, $cropped);
		}
		
		
		// return path
		return $thumbPath;
	}
	
	
	public function createThumbnailImage($thumbPath, $maxWidth, $maxHeight, $fillColor = false, $cropped = false)
	{
		$thumbWidth = $maxWidth;
		$thumbHeight = $maxHeight;
                
        if ($cropped && extension_loaded('imagick')) {
            if (static::$useFaceDetection && extension_loaded('facedetect')) {
                $cropper = new CropFace($this->FilesystemPath);
            } else {
                $cropper = new stojg\crop\CropEntropy($this->FilesystemPath);
            }
            $croppedImage = $cropper->resizeAndCrop($thumbWidth, $thumbHeight);
            $croppedImage->writeimage($thumbPath);
        } else {
        	$widthRatio = ($this->Width > $maxWidth) ? ($maxWidth / $this->Width) : 1;
    		$heightRatio = ($this->Height > $maxHeight) ? ($maxHeight / $this->Height) : 1;
                
        	// crop width/height to scale size if fill disabled
            if ($cropped) {
            	$ratio = max($widthRatio, $heightRatio);
            } else {
            	$ratio = min($widthRatio, $heightRatio);
            }
            
            $scaledWidth = round($this->Width * $ratio);
            $scaledHeight = round($this->Height * $ratio);
            
    		if (!$fillColor && !$cropped) {
            	$thumbWidth = $scaledWidth;
    			$thumbHeight = $scaledHeight;
    		}
            
    		// create images
    		$srcImage = $this->getImage();
    		$image = imagecreatetruecolor($thumbWidth, $thumbHeight);
    		
    		// paint fill color
    		if ($fillColor) {
    			// extract decimal values from hex triplet
    			$fillColor = sscanf($fillColor, '%2x%2x%2x');
    
    			// convert to color index
    			$fillColor = imagecolorallocate($image, $fillColor[0], $fillColor[1], $fillColor[2]);
    			
    			// fill background
    			imagefill($image, 0, 0, $fillColor);
    		} elseif( ($this->MIMEType == 'image/gif') || ($this->MIMEType == 'image/png' )) {
    			$trans_index = imagecolortransparent($srcImage);
    			
    			// check if there is a specific transparent color
    			if ($trans_index >= 0 && $trans_index < imagecolorstotal($srcImage)) {
    				$trans_color = imagecolorsforindex($srcImage, $trans_index);
    				
    				// allocate in thumbnail
    				$trans_index = imagecolorallocate($image, $trans_color['red'], $trans_color['green'], $trans_color['blue']);
    				
    				// fill background
    				imagefill($image, 0, 0, $trans_index);
    				imagecolortransparent($image, $trans_index);
    
    			} elseif($this->MIMEType == 'image/png') {
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

        	// resize photo to thumbnail
            if ($cropped) {
                imagecopyresampled(
        			  $image
        			, $srcImage
        			, ($thumbWidth - $scaledWidth) / 2, ($thumbHeight - $scaledHeight) / 2
            		, 0, 0
        			, $scaledWidth, $scaledHeight
        			, $this->Width, $this->Height
        		);
            } else {
        		imagecopyresampled(
        			  $image
        			, $srcImage
        			, round( ($thumbWidth - $scaledWidth) / 2 ), round( ($thumbHeight - $scaledHeight) / 2 )
        			, 0, 0
        			, $scaledWidth, $scaledHeight
        			, $this->Width, $this->Height
        		);
            }
            
    		// save thumbnail to disk
			switch ($this->ThumbnailMIMEType) {
				case 'image/gif':
					imagegif($image, $thumbPath);
					break;
				
				case 'image/jpeg':
					imagejpeg($image, $thumbPath, static::$thumbnailJPEGCompression);
					break;
					
				case 'image/png':
					imagepng($image, $thumbPath, static::$thumbnailPNGCompression);
					break;
					
				default:
					throw new Exception('Unhandled thumbnail format');		
			}
        }
    		
		chmod($thumbPath, static::$newFilePermissions);
        return true;
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
        // handle recieving a field array from $_FILES
        if (is_array($uploadedFile)) {
            if (isset($uploadedFile['error']) && $uploadedFile['error'] != ERR_UPLOAD_OK) {
                return null;
            }

            if (!empty($uploadedFile['name']) && empty($fieldValues['Caption'])) {
                $fieldValues['Caption'] = preg_replace('/\.[^.]+$/', '', $uploadedFile['name']);
            }

            $uploadedFile = $uploadedFile['tmp_name'];
        }

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
		$targetDirectory = dirname($Media->FilesystemPath);
		
		if(!is_dir($targetDirectory))
		{
			mkdir($targetDirectory, static::$newDirectoryPermissions, true);
		}
		
		// set file permissions
		if (rename($file, $Media->FilesystemPath))
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
			//MICS::dump(static::$mimeHandlers, 'MIME Handlers');
			throw new MediaTypeException('No class registered for mime type "' . $mediaInfo['mimeType'] . '"');
		}
		
		$mediaInfo['className'] = static::$mimeHandlers[$mediaInfo['mimeType']];
		
		// call registered type's analyzer
		$mediaInfo = call_user_func(array($mediaInfo['className'], 'analyzeFile'), $filename, $mediaInfo);
		
		return $mediaInfo;
	}
	
	static public function getBlankPath($contextClass)
	{
		$path = array('site-root','img',sprintf(static::$defaultFilenameFormat, $contextClass));
		
		if($node = Site::resolvePath($path))
		{
			return $node->RealPath;
		}
		else
		{
			throw new Exception('Could not load '.implode('/',$path));
		}
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