<?php

namespace Emergence\Docs;

use Emergence\OpenAPI\Reader as OpenAPIReader;
use Emergence\OpenAPI\Writer as OpenAPIWriter;
use Site;

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

        $openApiData = OpenAPIReader::readTree([
            'host' => Site::getConfig('primary_hostname'),
            'schemes' => $schemes
        ]);

        $openApiData = OpenAPIWriter::sort($openApiData);

        return static::respond('openAPI', $openApiData);
    }
}
