<?php

return $taskConfig = [
    'title' => 'Show status of migrations',
    'description' => 'Show the status of all available migration scripts',
    'icon' => 'import',
    'handler' => function () use ($taskConfig) {
        \Debug::dumpVar(\Site::$pathStack, false, 'handling request for' . $taskConfig['title']);
    }
];