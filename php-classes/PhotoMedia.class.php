<?php

class PhotoMedia extends Media
{
    // configurables
    public static $jpegCompression = 90;

    public static $mimeHandlers = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/tiff',
        'application/psd'
    ];

    // public methods
    public function getValue($name)
    {
        switch ($name) {
            case 'ThumbnailMIMEType':
                switch ($this->MIMEType) {
                    case 'application/psd':
                        return 'image/png';
                    case 'image/tiff':
                        return 'image/jpeg';
                    default:
                        return $this->MIMEType;
                }

            case 'Extension':

                switch ($this->MIMEType) {
                    case 'application/psd':
                        return 'psd';

                    case 'image/tiff':
                        return 'tif';

                    case 'image/gif':
                        return 'gif';

                    case 'image/jpeg':
                        return 'jpg';

                    case 'image/png':
                        return 'png';

                    default:
                        throw new Exception('Unable to find photo extension for mime-type: '.$this->MIMEType);
                }

            default:
                return parent::getValue($name);
        }
    }

    // static methods
    public static function analyzeFile($filename, $mediaInfo = [])
    {
        $mediaInfo = parent::analyzeFile($filename, $mediaInfo);

        if (!$mediaInfo['imageInfo'] = @getimagesize($filename)) {
            throw new Exception('Failed to read image file information');
        }

        // store image data
        $mediaInfo['width'] = $mediaInfo['imageInfo'][0];
        $mediaInfo['height'] = $mediaInfo['imageInfo'][1];
        $mediaInfo['duration'] = 0;

        return $mediaInfo;
    }
}