<?php

use Emergence\Connectors\Job;
use Emergence\SiteAdmin\MigrationsRequestHandler;

$logger = $_COMMAND['LOGGER'];


// parse args
if (empty($_COMMAND['ARGS'])) {
    die('Usage: migrations:execute <migration-key|--all>');
}

$migrationKeys = [];
$force = false;

foreach (preg_split('/\s+/', $_COMMAND['ARGS']) as $arg) {
    if ($arg == '--all') {
        $migrationKeys = '--all';
        continue;
    }

    if ($arg == '--force') {
        $force = true;
        continue;
    }

    if ($migrationKeys != '--all') {
        $migrationKeys[] = $arg;
    }
}

// load migration(s)
if ($migrationKeys == '--all') {
    $migrations = MigrationsRequestHandler::getMigrations();
} else {
    $migrations = [];

    foreach ($migrationKeys as $migrationKey) {
        $migration = MigrationsRequestHandler::getMigrationData($migrationKey);

        if (!$migration) {
            $logger->error("Migration not found: $migrationKey");
            exit(1);
        }

        $migrations[] = $migration;
    }
}


// run them all
foreach ($migrations as $migration) {
    if (!$force && $migration['status'] != MigrationsRequestHandler::STATUS_NEW) {
        $logger->info('Skipping migration with status {status}: {key}', $migration);
        continue;
    }

    $logger->info('Executing migration: {key}', $migration);
    $migration = MigrationsRequestHandler::executeMigration($migration, $force);

    if ($output = trim($migration['output'])) {
        $output = explode(PHP_EOL, $output);
        foreach ($output as $line) {
            $logger->debug($line);
        }
    }

    $logger->notice('Migration complete with result {status}: {key}', $migration);
}
