<?php

namespace Emergence\SiteAdmin;

class RequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');
        
        return static::respond('siteAdminIndex', array(
            'scripts' => array_filter(
                \Emergence_FS::getAggregateChildren('site-root/site-admin'),
                function($script) {
                    return $script->Handle != 'index.php';
                }
            )
        ));
    }
}