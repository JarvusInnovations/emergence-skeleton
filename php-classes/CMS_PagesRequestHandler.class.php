<?php

class CMS_PagesRequestHandler extends CMS_ContentRequestHandler
{
    // RecordsRequestHandler config
    static public $recordClass = 'CMS_Page';
    static public $browseConditions = array(
    	'Class' => 'CMS_Page'
    );
	

	static protected function throwRecordNotFoundError($handle, $message = 'Record not found')
	{
		return static::respond('pageNotFound', array(
			'success' => false
			,'pageHandle' => $handle
		));
	}
}