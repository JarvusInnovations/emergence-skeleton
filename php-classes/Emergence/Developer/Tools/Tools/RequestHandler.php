<?php
    namespace Emergence\Developer\Tools\Tools;

use Site,Emergence_FS;

class RequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        switch ($action ? $action : $action = static::shiftPath()) {
            case 'clear-apc-cache':
                return static::handleAPCClearRequest();

            case 'precache':
                return static::handlePrecacheRequest();

            case 'migrations':
                return \Emergence\Migrations\MigrationsRequestHandler::handleRequest();

            case 'table-manager':
                return \TableManagerRequestHandler::handleRequest();

            case 'clear-template-cache':
                return static::handleTemplatesClearRequest();

            default:
                return static::respond('home');
        }
    }

    public static function respond($responseID, $responseData = [], $responseMode = false)
    {
        \Emergence\Developer\Tools\RequestHandler::respond($responseID, array_merge($responseData,[

        ]),$responseMode);
    }

    public static function handleAPCClearRequest()
    {
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_REQUEST['target'] != 'System') {
                apc_clear_cache('user');
                $data['userClear'] = true;
            }

            if ($_REQUEST['target'] != 'User') {
                apc_clear_cache();
                $data['systemClear'] = true;
            }

            $data['clear'] = true;
        }

        static::respond('apc-clear', $data);
    }

    public static function handlePrecacheRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Site::$autoPull = true;
            Site::$debug = true;

            set_time_limit(0);

            $trees = [
                'dwoo-plugins'
                ,'event-handlers'
                ,'ext-library'
                ,'html-templates'
                ,'js-library'
                ,'php-classes'
                ,'php-config'
                ,'phpunit-tests'
                ,'php-migrations'
                ,'site-root'
                ,'sencha-workspace/.sencha'
                ,'sencha-workspace/microloaders'
                ,'sencha-workspace/pages'
                ,'sencha-workspace/packages'
                ,'sencha-workspace/EmergenceEditor'
                ,'sencha-workspace/EmergencePullTool'
                ,'sencha-workspace/ContentEditor'
            ];

            $message = "";
            foreach ($_POST['collections'] AS $collection) {
                $filesCached = Emergence_FS::cacheTree($collection, true);
                $message .= sprintf('Precached %03u files in %s'.PHP_EOL, $filesCached, $collection);
            }
        }

        static::respond('precache', [
            'message' => $message
        ]);
    }

    public static function handleTemplatesClearRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $templateDir = \Emergence\Dwoo\Engine::$pathCompile.'/'.Site::getConfig('handle');

            exec("find $templateDir -name \"*.d*.php\" -type f -delete");

            $success = true;
        }

        static::respond('templates-clear', [
            'success' => $success
        ]);
    }
}