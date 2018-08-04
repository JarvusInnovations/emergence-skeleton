<?php

namespace Emergence\WebApps;

use Exception;

use Site;
use Cache;
use Emergence\Site\Response;


class SenchaApp extends App
{
    public static $plugins = [];


    protected $manifest;


    public static function load($name)
    {
        $cacheKey = "sencha-app/{$name}";

        if (!$manifest = Cache::fetch($cacheKey)) {
            // TODO: create cache clear event
            $manifestNode = Site::resolvePath([static::$buildsRoot, $name, 'app.json']);

            if (!$manifestNode) {
                return null;
            }

            $manifest = json_decode(file_get_contents($manifestNode->RealPath), true);

            Cache::store($cacheKey, $manifest);
        }

        return new static($name, $manifest);
    }


    public function __construct($name, array $manifest)
    {
        parent::__construct($name);

        $this->manifest = $manifest;
    }

    public static function getPlugins()
    {
        return static::$plugins;
    }

    public function render()
    {
        return new Response('sencha', [
            'app' => $this
        ]);
    }

    public function buildCssMarkup()
    {
        $baseUrl = $this->getUrl();

        $html = [];

        foreach ($this->manifest['css'] as $css) {
            $html[] = '<link rel="stylesheet" href="'.$this->getAssetUrl($css['path']).'"/>';
        }

        return implode(PHP_EOL, $html);
    }

    public function buildJsMarkup()
    {
        $baseUrl = $this->getUrl();

        $html = [];

        foreach ($this->manifest['js'] as $js) {
            $html[] = '<script type="text/javascript\" src="'.$this->getAssetUrl($js['path']).'"></script>';
        }

        // TODO: migrate away from /app request handler
        foreach ($this->getPlugins() as $packageName) {
            $node = Site::resolvePath(['sencha-workspace', 'packages', $packageName, 'build', "{$packageName}.json"]);

            if (!$node) {
                throw new Exception("build for sencha plugin {$packageName} not found");
            }

            $html[] = "<script type=\"text/javascript\" src=\"/app/packages/{$packageName}/build/{$packageName}.json?_sha1={$node->SHA1}\"></script>";
        }

        return implode(PHP_EOL, $html);
    }
}