<?php

namespace Emergence\SiteAdmin;


class SourcesRequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        return static::respond('sources');
    }
}