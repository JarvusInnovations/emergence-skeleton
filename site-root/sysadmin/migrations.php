<?php

$GLOBALS['Session']->requireAccountLevel('Developer');


if (!$migrationName = array_shift(Site::$pathStack)) {
    Emergence_FS::cacheTree('php-classes/Migrations');
    $migrationsCollection = Site::resolvePath('php-classes/Migrations');

    print('<ul>');
    foreach ($migrationsCollection->getChildren() AS $migrationNode) {
        if (!is_a($migrationNode, SiteFile)) {
            continue;
        }
        $migrationName = basename($migrationNode->Handle, '.php');
        $class = new ReflectionClass("Migrations\\$migrationName");
        
        if ($class->isAbstract()) {
            continue;
        }
        
        print('<li><a href="/sysadmin/migrations/'.$migrationName.'">'.$migrationName.'</a></li>');
    }
    print('</ul>');
    exit();
}

// TODO: track already-run migrations in a table

$migrationName = 'Migrations\\'.$migrationName;

header('Content-Type: text/plain');
$migrationName::$printLog = empty($_GET['quiet']);
$migrationName::$pretend = !empty($_GET['pretend']);
$migrationName::upgrade();