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
        $schemes = ['http'];

        if (Site::getConfig('ssl')) {
            array_unshift($schemes, 'https');
        }

        $openApiData = DataUtil::mergeFileTree('api-docs', [
            'host' => Site::getConfig('primary_hostname'),
            'schemes' => $schemes
        ]);

        $openApiData = OpenAPIWriter::sort($openApiData);

        return static::respond('openAPI', $openApiData);
    }
}