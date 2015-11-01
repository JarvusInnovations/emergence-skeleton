<?php

class CMS_TextContent extends CMS_ContentItem
{
    public function renderBody()
    {
        return '<div class="content-item content-text" id="contentItem-'.$this->ID.'">'.nl2br(htmlspecialchars($this->Data)).'</div>';
    }
}