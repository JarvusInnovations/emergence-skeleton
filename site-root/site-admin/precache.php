<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    Site::$autoPull = true;
    Site::$debug = true;

    set_time_limit(0);

    $message = "";
    foreach ($_POST['collections'] AS $collection) {
        if (!$collection = trim($collection)) {
            continue;
        }

        $filesCached = Emergence_FS::cacheTree($collection, true);
        $message .= sprintf('Precached %03u files in %s'.PHP_EOL, $filesCached, $collection);
    }
}

RequestHandler::respond('precache', array(
    'message' => $message
));