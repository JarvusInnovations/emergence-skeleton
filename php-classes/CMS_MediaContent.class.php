<?php

class CMS_MediaContent extends CMS_ContentItem
{
	static public $thumbWidth = 400;
	static public $thumbHeight = 400;

	static public $fullWidth = 1000;
	static public $fullHeight = 1000;

	public function renderBody()
	{
		if(empty($this->Data['MediaID']) || !($Media = Media::getByID($this->Data['MediaID'])))
			return '';
		else
			return '<div class="content-item content-media" id="contentItem-'.$this->ID.'">'
				.static::getMediaMarkup($Media)
				.'</div>';
	}
	
	static public function getMediaMarkup(Media $Media)
	{
		switch($Media->Class)
		{
			case 'AudioMedia':
				return '<a href="'.$Media->WebPath.'" title="'.htmlspecialchars($Media->Caption).'" class="media-link audio-link">'
					.'<img src="'.$Media->getThumbnailRequest(static::$thumbWidth,static::$thumbHeight).'" alt="'.htmlspecialchars($Media->Caption).'">'
					.'</a>';

			case 'VideoMedia':
				return '<a href="'.$Media->WebPath.'" title="'.htmlspecialchars($Media->Caption).'" class="media-link video-link">'
					.'<img src="'.$Media->getThumbnailRequest(static::$thumbWidth,static::$thumbHeight).'" alt="'.htmlspecialchars($Media->Caption).'">'
					.'</a>';

			case 'PDFMedia':
				return '<a href="'.$Media->WebPath.'" title="'.htmlspecialchars($Media->Caption).'" class="media-link pdf-link">'
					.'<img src="'.$Media->getThumbnailRequest(static::$thumbWidth,static::$thumbHeight).'" alt="'.htmlspecialchars($Media->Caption).'">'
					.'</a>';
					
			case 'PhotoMedia':
			default:
				return '<a href="'.$Media->getThumbnailRequest(static::$fullWidth,static::$fullHeight).'" title="'.htmlspecialchars($Media->Caption).'" class="media-link image-link">'
					.'<img src="'.$Media->getThumbnailRequest(static::$thumbWidth,static::$thumbHeight).'" alt="'.htmlspecialchars($Media->Caption).'">'
					.'</a>';
		}
	}
}