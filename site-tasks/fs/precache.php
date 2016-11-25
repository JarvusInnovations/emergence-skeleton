<?php

return [
    'title' => 'Precache parent tree',
    'description' => 'Scan parent site for new files and cache them locally',
    'icon' => 'transfer',
    'requireAccountLevel' => 'Administrator',
    'handleRequest' => function () {
        \Debug::dumpVar(\Site::$pathStack, false, 'handling request for precache');
    }
];