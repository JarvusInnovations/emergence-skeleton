<?php


class CMS_DraftsRequestHandler extends RequestHandler
{
    public static function handleRequest()
    {
        global $Session;

        $Session->requireAuthentication();

        if (static::peekPath()=='json') {
            static::$responseMode = static::shiftPath();
        }

        return static::respond('drafts', array(
            'success' => true
            ,'data' => CMS_Content::getAllByWhere(array(
                'AuthorID'=>$Session->PersonID
                ,'Status = "Draft" OR Published > CURRENT_TIMESTAMP'
            ))
        ));
    }
}