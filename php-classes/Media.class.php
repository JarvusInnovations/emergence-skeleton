<?php

abstract class Media extends ActiveRecord
{
    public static $useCache = true;
    public static $singularNoun = 'media item';
    public static $pluralNoun = 'media items';

    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = PhotoMedia::class;
    public static $subClasses = [
        __CLASS__,
        PhotoMedia::class,
        AudioMedia::class,
        VideoMedia::class,
        PDFMedia::class
    ];

    public static $collectionRoute = '/media';

    public static $tableName = 'media';

    public static $fields = [
        'ContextClass' => [
            'type' => 'string',
            'notnull' => false
        ],
        'ContextID' => [
            'type' => 'integer',
            'notnull' => false
        ],
        'MIMEType' => 'string',
        'Width' => [
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => false
        ],
        'Height' => [
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => false
        ],
        'Duration' => [
            'type' => 'float',
            'unsigned' => true,
            'notnull' => false
        ],
        'Caption' => [
            'type' => 'string',
            'notnull' => false
        ]
    ];

    public static $relationships = [
        'Creator' => [
            'type' => 'one-one',
            'class' => \Emergence\People\Person::class,
            'local' => 'CreatorID'
        ],
        'Context' => [
            'type' => 'context-parent'
        ]
    ];

    public static $searchConditions = [
        'Caption' => [
            'qualifiers' => ['any','caption'],
            'points' => 2,
            'sql' => 'Caption LIKE "%%%s%%"'
        ],
        'CaptionLike' => [
            'qualifiers' => ['caption-like'],
            'points' => 2,
            'sql' => 'Caption LIKE "%s"'
        ],
        'CaptionNot' => [
            'qualifiers' => ['caption-not'],
            'points' => 2,
            'sql' => 'Caption NOT LIKE "%%%s%%"'
        ],
        'CaptionNotLike' => [
            'qualifiers' => ['caption-not-like'],
            'points' => 2,
            'sql' => 'Caption NOT LIKE "%s"'
        ]
    ];

    public static $validators = [
        'SourceFile' => [
            'validator' => [__CLASS__, 'validateSourceFile']
        ],
        'MIMEType' => [
            'validator' => [__CLASS__, 'validateMIMEType']
        ]
    ];

    public static $webPathFormat = '/media/open/%u'; // 1=mediaID
    public static $thumbnailRequestFormat = '/thumbnail/%1$u/%2$ux%3$u%4$s'; // 1=media_id 2=width 3=height 4=fill_color
    public static $blankThumbnailRequestFormat = '/thumbnail/%1$s/%2$ux%3$u%4$s'; // 1=class 2=width 3=height 4=fill_color
    public static $thumbnailJPEGCompression = 90;
    public static $thumbnailPNGCompression = 9;
    public static $defaultFilenameFormat = 'default.%s.jpg';
    public static $newDirectoryPermissions = 0775;
    public static $newFilePermissions = 0664;
    public static $magicPath = null; //'/usr/share/misc/magic.mgc';
    public static $useFaceDetection = true;

    public static $mimeHandlers = [];

    public static $mimeRewrites = [
        'image/photoshop'               => 'application/psd',
        'image/x-photoshop'            => 'application/psd',
        'image/psd'                    => 'application/psd',
        'application/photoshop'        => 'application/psd',
        'image/vnd.adobe.photoshop'    => 'application/psd'
    ];

     // known MIMEType - file extension mappings
    public static $mimeTypeMappings = [
        // audio
        'audio/mpeg' => 'mp3',
        // video
        'video/x-flv' => 'flv',
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        // image & pdf
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/tiff' => 'tif',
        'application/psd' => 'psd',
        'application/postscript' => 'eps',
        'image/svg+xml' => 'svg',
        'application/pdf' => 'pdf'
    ];

    // privates
    protected $_webPath;
    protected $_filesystemPath;
    protected $_mediaInfo;
    protected $_sourceFile;

    public static function __classLoaded()
    {
        parent::__classLoaded();

        // load subclasses
        foreach (static::$subClasses AS $subClass) {
            Site::loadClass($subClass);
        }
    }

    // magic methods
    function getValue($name)
    {
        switch ($name) {
            case 'Data':
            case 'SummaryData':
            case 'JsonTranslation':
                return [
                    'ID' => $this->ID,
                    'Class' => $this->Class,
                    'ContextClass' => $this->ContextClass,
                    'ContextID' => $this->ContextID,
                    'MIMEType' => $this->MIMEType,
                    'Width' => $this->Width,
                    'Height' => $this->Height,
                    'Duration' => $this->Duration
                ];

            case 'Filename':
                return $this->getFilename();

            case 'ThumbnailMIMEType':
                return $this->MIMEType;

            case 'Extension':
                throw new MediaTypeException('Unable to find extension for mime-type: ' . $this->MIMEType);

            case 'WebPath':

                if (!isset($this->_webPath)) {
                    $this->_webPath = sprintf(
                        static::$webPathFormat
                        , $this->ID
                    );
                }

                return $this->_webPath;


            case 'FilesystemPath':
                return $this->getFilesystemPath();


            case 'BlankPath':

                return static::getBlankPath($this->ContextClass);

            default:
                return parent::getValue($name);
        }
    }

    // public methods
    public function save($deep = true)
    {
        parent::save($deep);

        if ($this->_sourceFile) {
            $this->writeFile($this->_sourceFile);
        }
    }

    public function getSourceFile()
    {
        return $this->_sourceFile;
    }

    public function setSourceFile($sourceFile)
    {
        return $this->_sourceFile = $sourceFile;
    }

    public static function getBlankThumbnailRequest($class, $width, $height, $fillColor = null)
    {
        return sprintf(
            static::$blankThumbnailRequestFormat,
            $class,
            $width,
            $height,
            ( isset($fillColor) ? 'x'.$fillColor : '' )
        );
    }

    public function getThumbnailRequest($width, $height = null, $fillColor = null, $cropped = false)
    {
        return sprintf(
            static::$thumbnailRequestFormat,
            $this->ID,
            $width,
            $height ?: $width,
            ( is_string($fillColor) ? 'x'.$fillColor : '' )
        ) . ($cropped ? '/cropped' : '');
    }

    public function getImage($sourceFile = null)
    {
        if (!isset($sourceFile)) {
            $sourceFile = $this->FilesystemPath ? $this->FilesystemPath : $this->BlankPath;
        }

        switch ($this->MIMEType) {
            case 'application/psd':
            case 'image/tiff':

                //Converts PSD to PNG temporarily on the real file system.
                $tempFile = tempnam('/tmp', 'media_convert');
                exec("convert -density 100 " . $this->FilesystemPath . "[0] -flatten $tempFile.png");

                return imagecreatefrompng("$tempFile.png");

            case 'application/pdf':

               return PDFMedia::getImage($sourceFile);

            case 'application/postscript':

                return imagecreatefromstring(shell_exec("gs -r150 -dEPSCrop -dNOPAUSE -dBATCH -sDEVICE=png48 -sOutputFile=- -q $this->FilesystemPath"));

            default:

                if (!$fileData = @file_get_contents($sourceFile)) {
                    throw new Exception('Could not load media source: '.$sourceFile);
                }

                $image = imagecreatefromstring($fileData);

                if ($this->MIMEType == 'image/jpeg' && ($exifData = @exif_read_data($sourceFile)) && !empty($exifData['Orientation'])) {
                    switch ($exifData['Orientation']) {
                        case 1: // nothing
                            break;
                        case 2: // horizontal flip
                            imageflip($image, IMG_FLIP_HORIZONTAL); // TODO: need PHP 5.3 compat method
                            break;
                        case 3: // 180 rotate left
                            $image = imagerotate($image, 180, null);
                            break;
                        case 4: // vertical flip
                            imageflip($image, IMG_FLIP_VERTICAL); // TODO: need PHP 5.3 compat method
                            break;
                        case 5: // vertical flip + 90 rotate right
                            imageflip($image, IMG_FLIP_VERTICAL); // TODO: need PHP 5.3 compat method
                            $image = imagerotate($image, -90, null);
                            break;
                        case 6: // 90 rotate right
                            $image = imagerotate($image, -90, null);
                            break;
                        case 7: // horizontal flip + 90 rotate right
                            imageflip($image, IMG_FLIP_HORIZONTAL); // TODO: need PHP 5.3 compat method
                            $image = imagerotate($image, -90, null);
                            break;
                        case 8: // 90 rotate left
                            $image = imagerotate($image, 90, null);
                            break;
                    }
                }

                return $image;
        }

    }

    public function getThumbnail($maxWidth, $maxHeight, $fillColor = false, $cropped = false)
    {
        // init thumbnail path
        $thumbFormat = sprintf('%ux%u', $maxWidth, $maxHeight);

        if ($fillColor) {
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

        if ($cropped && extension_loaded('imagick') && $this->FilesystemPath) {
            $originalTimeLimit = ini_get('max_execution_time');

            // check for existing facedetect job
            $cacheKey = "facedetect:{$thumbPath}";
            $faceDetectTime = Cache::fetch($cacheKey);

            // a parallel or dead worker is already working on this thumb
            if ($faceDetectTime) {
                // wait for existing job to finish or timeout
                while (time() - $faceDetectTime < static::$faceDetectionTimeLimit) {
                    sleep(1);
                }

                // other worker succeeded, we're done
                if (file_exists($thumbPath)) {
                    return true;
                }

                // disable face detection because it already failed for this thumb
                static::$useFaceDetection = false;
            }

            if (static::$useFaceDetection && extension_loaded('facedetect')) {
                $cropper = new CropFace($this->FilesystemPath);
            } else {
                $cropper = new stojg\crop\CropEntropy($this->FilesystemPath);
            }
            $croppedImage = $cropper->resizeAndCrop($thumbWidth, $thumbHeight);
            $croppedImage->writeimage($thumbPath);
        } else {
            // load source image
            $srcImage = $this->getImage();
            $srcWidth = imagesx($srcImage);
            $srcHeight = imagesy($srcImage);

            // calculate
            if ($srcWidth && $srcHeight) {
                $widthRatio = ($srcWidth > $maxWidth) ? ($maxWidth / $srcWidth) : 1;
                $heightRatio = ($srcHeight > $maxHeight) ? ($maxHeight / $srcHeight) : 1;

                // crop width/height to scale size if fill disabled
                if ($cropped) {
                    $ratio = max($widthRatio, $heightRatio);
                } else {
                    $ratio = min($widthRatio, $heightRatio);
                }

                $scaledWidth = round($srcWidth * $ratio);
                $scaledHeight = round($srcHeight * $ratio);
            } else {
                $scaledWidth = $maxWidth;
                $scaledHeight = $maxHeight;
            }

            if (!$fillColor && !$cropped) {
                $thumbWidth = $scaledWidth;
                $thumbHeight = $scaledHeight;
            }

            // create thumbnail images
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
                    , $srcWidth, $srcHeight
                );
            } else {
                imagecopyresampled(
                      $image
                    , $srcImage
                    , round( ($thumbWidth - $scaledWidth) / 2 ), round( ($thumbHeight - $scaledHeight) / 2 )
                    , 0, 0
                    , $scaledWidth, $scaledHeight
                    , $srcWidth, $srcHeight
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

    // static methods
    public static function validateSourceFile(RecordValidator $Validator, ActiveRecord $Record, $validatorOptions = [], $validatorKey)
    {
        if (empty($Validator->hasErrors($validatorKey))) {
            if ($Record->isPhantom && empty($Record->getSourceFile())) {
                $Validator->addError($validatorKey, 'Source file not set');
            }
        }
    }

    public static function validateMIMEType(RecordValidator $Validator, ActiveRecord $Record, $validatorOptions = [], $validatorKey)
    {
        if (empty($Validator->hasErrors($validatorKey))) {
            $mimeHandlers = $Record::getStackedConfig('mimeHandlers');
            if (empty($Record->MIMEType) || !in_array($Record->MIMEType, $mimeHandlers)) {
                if (empty($Record->MIMEType)) {
                    $errorMessage = 'File type could not be found. Please try again or use another file.';
                } else {
                    $mappedMimeTypes = array_map(function($mime) {
                        return static::$mimeTypeMappings[$mime];
                    }, $mimeHandlers);
                    $errorMessage = sprintf('File type invalid. Please try again with a file with one of the following extensions: (.%s)"', join(', .', array_unique(array_filter($mappedMimeTypes))));
                }

                $Validator->addError($validatorKey, $errorMessage);
            }
        }
    }

    public static function createFromUpload($uploadedFile, $fieldValues = array(), $autoSave = false)
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
        if (!is_uploaded_file($uploadedFile)) {
            throw new Exception('Supplied file is not a valid upload');
        }

        return static::createFromFile($uploadedFile, $fieldValues, $autoSave);
    }

    public static function createFromFile($file, $fieldValues = array(), $autoSave = false)
    {
        try {
            $calledClass = get_called_class();
            if ($calledClass == Media::class) {
                // analyze file to create media object
                $defaultClass = Media::getStaticDefaultClass();
                $mediaInfo = static::analyzeFile($file);
                // analyze file to guess subclass match
                if (!empty($mediaInfo) && !empty($mediaInfo['className']) && $mediaInfo['className'] != $defaultClass && is_subclass_of($mediaInfo['className'], Media::class)) {
                    $Media = $mediaInfo['className']::create($fieldValues);
                    $mediaInfo = $Media::analyzeFile($file, $mediaInfo);
                } else {
                    $Media = $defaultClass::create($fieldValues);
                }
            } else {
                // use media subclasses to create media object
                $Media = $calledClass::create($fieldValues);
                $mediaInfo = $Media::analyzeFile($file);
            }

            // cache source file
            $Media->setSourceFile($file);
            // init media
            $Media->initializeFromAnalysis($mediaInfo);

            // save media & write file
            if ($autoSave === true) {
                $Media->save();
            }

            return $Media;

        } catch (Exception $e) {
            \Emergence\Logger::general_warning('Caught exception while processing media upload, aborting upload and returning null', array(
                'exceptionClass' => get_class($e)
                ,'exceptionMessage' => $e->getMessage()
                ,'exceptionCode' => $e->getCode()
                ,'recordData' => $Media ? $Media->getData() : null
                ,'mediaInfo' => $mediaInfo
            ));
            // fall through to cleanup below
            throw $e;
        }

        return null;
    }

    public function initializeFromAnalysis($mediaInfo)
    {
        $this->MIMEType = $mediaInfo['mimeType'];
        $this->Width = $mediaInfo['width'];
        $this->Height = $mediaInfo['height'];
        $this->Duration = $mediaInfo['duration'];
    }

    public static function analyzeFile($filename, $mediaInfo = [])
    {
        // check file
        if (!is_readable($filename)) {
            throw new Exception('Unable to read media file for analysis: "'.$filename.'"');
        }

        // get mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE, static::$magicPath);

        if (!$finfo || !($mimeType = finfo_file($finfo, $filename)) ) {
            throw new Exception('Unable to load media file info');
        }

        finfo_close($finfo);

        // dig deeper if only generic mimetype returned
        if ($mimeType == 'application/octet-stream') {
            $finfo = finfo_open(FILEINFO_NONE, static::$magicPath);

            if (!$finfo || !($fileInfo = finfo_file($finfo, $filename)) ) {
                throw new Exception('Unable to load media file info');
            }

            finfo_close($finfo);

            // detect EPS
            if (preg_match('/^DOS EPS/i', $fileInfo)) {
                $mimeType = 'application/postscript';
            }
        } elseif (array_key_exists($mimeType, static::$mimeRewrites)) {
            $mimeType = static::$mimeRewrites[$mimeType];
        }

        // compile mime data
        $mediaInfo['mimeType'] = $mimeType;

        if (!isset($mediaInfo['className']) && static::getMimeHandlerClass($mimeType)) {
            $mediaInfo['className'] = static::getMimeHandlerClass($mimeType);
        }

        return $mediaInfo;
    }

    public static function getMimeHandlerClass($mimeType)
    {
        $className = null;
        foreach (static::$subClasses as $subClass) {
            if (in_array($mimeType, $subClass::$mimeHandlers)) {
                $className = $subClass;
                break;
            }
        }
        return $className;
    }

    public static function getBlankPath($contextClass)
    {
        $path = [
            'site-root',
            'img',
            sprintf(static::$defaultFilenameFormat, $contextClass)
        ];

        if ($node = Site::resolvePath($path)) {
            return $node->RealPath;
        } else {
            throw new Exception('Could not load '.implode('/',$path));
        }
    }

    public static function getBlank($contextClass)
    {
        // get image info
        $sourcePath = static::getBlankPath($contextClass);
        $sourceInfo = @getimagesize($sourcePath);

        if (!$sourceInfo) {
            throw new Exception("Unable to load blank image for context '$contextClass' from '$sourcePath'");
        }

        // get mime type
        $mimeType = image_type_to_mime_type($sourceInfo[2]);

        // determine type
        if(empty(static::getMimeHandlerClass($mimeType))) {
            throw new MediaTypeException('No class registered for mime type "' . $mimeType . '"');
        }

        $className = static::getMimeHandlerClass($mimeType);

        $blankMedia = new $className();
        $blankMedia->ContextClass = $contextClass;
        $blankMedia->MIMEType = $mimeType;
        $blankMedia->Width = $sourceInfo[0];
        $blankMedia->Height = $sourceInfo[1];

        return $blankMedia;

    }

    public static function getSupportedTypes()
    {
        return array_unique(array_merge(static::$mimeHandlers, array_keys(static::$mimeRewrites)));
    }

    public function getFilesystemPath($variant = 'original', $filename = null)
    {
        if ($this->isPhantom) {
            return $this->getSourceFile();
        }

        return Site::$rootPath . '/site-data/media/' . $variant . '/' . ($filename ?: $this->getFilename($variant));
    }

    public function getFilename($variant = 'original')
    {
        if ($this->isPhantom) {
            return 'default.' . $this->Extension;
        }

        return $this->ID . '.' . $this->Extension;
    }

    public function getMIMEType($variant = 'original')
    {
        return $this->MIMEType;
    }

    public function writeFile($sourceFile)
    {
        $targetDirectory = dirname($this->FilesystemPath);

        // create target directory if needed
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, static::$newDirectoryPermissions, true);
        }

        // move source file to target path
        if (!rename($sourceFile, $this->FilesystemPath)) {
            throw new \Exception('Failed to move source file to destination');
        }

        // set file permissions
        chmod($this->FilesystemPath, static::$newFilePermissions);
    }

    public function isVariantAvailable($variant)
    {
        return false;
    }
}
