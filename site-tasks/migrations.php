<?php

return $taskConfig = [
    'title' => 'Browse migrations',
    'description' => 'Show the status of all available migration scripts',
    'icon' => 'import',
    'handler' => Emergence\Migrations\MigrationsRequestHandler::class
];