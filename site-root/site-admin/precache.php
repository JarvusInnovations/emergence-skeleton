<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    Site::$autoPull = true;
    Site::$debug = true;

    set_time_limit(0);

    $trees = array(
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
    );

    $message = "";
    foreach ($_POST['collections'] AS $collection) {
        $filesCached = Emergence_FS::cacheTree($collection, true);
        $message .= sprintf('Precached %03u files in %s'.PHP_EOL, $filesCached, $collection);
    }
}

RequestHandler::respond('precache', array(
    'message' => $message
));