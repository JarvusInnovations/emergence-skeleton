<?php

namespace Emergence\SiteAdmin;

class RequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        \Emergence_FS::cacheTree('site-root/site-admin');

        return static::respond('siteAdminIndex', array(
            'scripts' => array_filter(
                \Emergence_FS::getAggregateChildren('site-root/site-admin'),
                function($script) {
                    return $script->Handle != '_index.php';
                }
            )
        ));
    }
}