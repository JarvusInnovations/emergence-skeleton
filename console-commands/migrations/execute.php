<?php

use Emergence\Connectors\Job;
use Emergence\SiteAdmin\MigrationsRequestHandler;

$logger = $_COMMAND['LOGGER'];


// load migration(s)
if (empty($_COMMAND['ARGS'])) {
    die('Usage: migrations:execute <migration-key|--all>');
}

$migrationKey = $_COMMAND['ARGS'];

if ($migrationKey == '--all') {
    $migrations = MigrationsRequestHandler::getMigrations();
} else {
    $migration = MigrationsRequestHandler::getMigrationData($migrationKey);

    if (!$migration) {
        $logger->error("Migration not found: $migrationKey");
        exit(1);
    }

    $migrations = [ $migration ];
}


// run them all
foreach ($migrations as $migration) {
    if ($migration['status'] != MigrationsRequestHandler::STATUS_NEW) {
        $logger->info('Skipping migration with status {status}: {key}', $migration);
        continue;
    }

    $logger->info('Executing migration: {key}', $migration);
    $migration = MigrationsRequestHandler::executeMigration($migration);

    if ($output = trim($migration['output'])) {
        $output = explode(PHP_EOL, $output);
        foreach ($output as $line) {
            $logger->debug($line);
        }
    }

    $logger->notice('Migration complete with result {status}: {key}', $migration);
}
