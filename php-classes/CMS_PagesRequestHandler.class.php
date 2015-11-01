<?php

class CMS_PagesRequestHandler extends CMS_ContentRequestHandler
{
    // RecordsRequestHandler config
    public static $recordClass = 'CMS_Page';
    public static $browseConditions = array(
        'Class' => 'CMS_Page'
    );


    protected static function throwRecordNotFoundError($handle, $message = 'Record not found')
    {
        return static::respond('pageNotFound', array(
            'success' => false
            ,'pageHandle' => $handle
        ));
    }
}