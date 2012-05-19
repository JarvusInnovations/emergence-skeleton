<?php

class CMS_RichTextContent extends CMS_ContentItem
{
	public function renderBody()
	{
		return '<div class="content-item content-richtext" id="contentItem-'.$this->ID.'">'.$this->Data.'</div>';
	}
}