<?php

namespace Emergence\Docs;

use Site;
use Emergence\OpenAPI\Writer AS OpenAPIWriter;
use Emergence\Util\Data AS DataUtil;


class ApiDocsRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json',
        'application/x-yaml' => 'yaml'
    ];

    public static function handleRequest()
    {
        $openApiData = DataUtil::mergeFileTree('api-docs', [
            'host' => Site::getConfig('primary_hostname')
        ]);

        $openApiData = OpenAPIWriter::sort($openApiData);

        return static::respond('docs', $openApiData);
    }
}