<?php

namespace Emergence\Docs;

use Site;
use Emergence_FS;

class ApiDocsRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json',
        'application/x-yaml' => 'yaml'
    ];

    public static function handleRequest()
    {
        $yml = [
            'host' => Site::getConfig('primary_hostname')
        ];

        $docsTree = Emergence_FS::findFiles('\.yml', true, 'api-docs');
#        \Debug::dumpVar($docsTree, false, 'docs tree');

        foreach ($docsTree AS $path => $node) {
            $pathStack = array_slice($node->getFullPath(), 1);
            $ymlRoot = &$yml;

            if ($pathStack[0] == 'paths') {
                $pathStackLast = array_pop($pathStack);

                if ($pathStackLast[0] != '_') {
                    $pathStack[] = substr($pathStackLast, 0, -4);
                }

                $ymlRoot = &$ymlRoot[array_shift($pathStack)]['/'.implode('/', $pathStack)];
            } else {
                while (count($pathStack) > 1) {
                    $ymlRoot = &$ymlRoot[array_shift($pathStack)];
                }

                if ($pathStack[0][0] != '_') {
                    $ymlRoot = &$ymlRoot[substr($pathStack[0], 0, -4)];
                }
            }

            $nodeYml = \Emergence\OpenAPI\Reader::read($node);
            $ymlRoot = $ymlRoot ? array_replace_recursive($ymlRoot, $nodeYml) : $nodeYml;
        }

        $yml = \Emergence\OpenAPI\Writer::sort($yml);

        return static::respond('docs', $yml);
    }
}