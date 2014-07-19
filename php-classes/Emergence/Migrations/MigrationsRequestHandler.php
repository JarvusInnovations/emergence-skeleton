<?php

namespace Emergence\Migrations;

use DB;
use Site;
use Emergence_FS;
use TableNotFoundException;

class MigrationsRequestHandler extends \RequestHandler
{
    const STATUS_NEW = 'new';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_STARTED = 'started';
    const STATUS_FAILED = 'failed';
    const STATUS_EXECUTED = 'executed';

    public static $userResponseModes = array(
        'application/json' => 'json'
        ,'text/csv' => 'csv'
    );

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        if (count(array_filter($migrationKey = static::getPath()))) {
            return static::handleMigrationRequest(implode('/', $migrationKey));
        }
        
        return static::handleBrowseRequest();
    }

    public static function handleBrowseRequest()
    {
        // get all migration status records from table
        try {
            $migrationRecords = DB::table('Key', 'SELECT * FROM _e_migrations');
        } catch (TableNotFoundException $e) {
            $migrationRecords = array();
        }

        Emergence_FS::cacheTree('php-migrations', !empty($_GET['refresh']));
        $migrations = array();

        // append sequence to each node
        foreach (Emergence_FS::getTreeFiles('php-migrations') AS $migrationPath => $migrationNodeData) {
            $migrationKey = preg_replace('#^php-migrations/(.*)\.php$#i', '$1', $migrationPath);
            $migrationRecord = array_key_exists($migrationKey, $migrationRecords) ? $migrationRecords[$migrationKey] : null;

            $migrations[$migrationKey] = array(
                'key' => $migrationKey
                ,'path' => 'php-migrations/' . $migrationKey . '.php'
                ,'status' => $migrationRecord ? $migrationRecord['Status'] : static::STATUS_NEW
                ,'executed' => $migrationRecord ? $migrationRecord['Timestamp'] : null
                ,'sha1' => $migrationNodeData['SHA1']
                ,'sequence' => preg_match('#(\d+)_[^/]+$#', $migrationKey, $matches) ? (int)$matches[1] : 0
            );
        }

        // sort migrations by sequence
        uasort($migrations, function($a, $b) {
            if ($a['sequence'] == $b['sequence']) {
                return 0;
            }
            return ($a['sequence'] < $b['sequence']) ? -1 : 1;
        });

        return static::respond('migrations', array(
            'data' => $migrations
        ));
    }

    public static function handleMigrationRequest($migrationKey)
    {
        $migrationPath = 'php-migrations/' . $migrationKey . '.php';
        $migrationNode = Site::resolvePath($migrationPath);
        
        if (!$migrationNode) {
            return static::throwNotFoundError('Migration not found');
        }

        try {
            $migrationRecord = DB::oneRecord('SELECT * FROM _e_migrations WHERE `Key` = "%s"', DB::escape($migrationKey));
        } catch (TableNotFoundException $e) {
            $migrationRecord = null;
        }

        $migration = array(
            'key' => $migrationKey
            ,'path' => $migrationPath
            ,'status' => $migrationRecord ? $migrationRecord['Status'] : static::STATUS_NEW
            ,'executed' => $migrationRecord ? $migrationRecord['Timestamp'] : null
            ,'sha1' => $migrationNode->SHA1
        );


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            if ($migrationRecord) {
                return static::throwError('Cannot execute requested migration, it has already been skipped, started, or executed');
            }
            
            $insertSql = sprintf('INSERT INTO `_e_migrations` SET `Key` = "%s", SHA1 = "%s", Timestamp = FROM_UNIXTIME(%u), Status = "%s"', $migrationKey, $migrationNode->SHA1, time(), static::STATUS_STARTED);
            
            try {
                DB::nonQuery($insertSql);
            } catch (TableNotFoundException $e) {
                static::createMigrationsTable();
                DB::nonQuery($insertSql);
            }

            \Site::$debug = true;
            $debugLogStartIndex = count(\Debug::$log);

            ob_start();
            $migrationStatus = include($migrationNode->RealPath);
            $output = ob_get_clean();
            
            if (!in_array($migrationStatus, array(static::STATUS_SKIPPED, static::STATUS_EXECUTED))) {
                $migrationStatus = static::STATUS_FAILED;
            }

            $migration['executed'] = time();
            $migration['status'] = $migrationStatus;
            $log = array_slice(\Debug::$log, $debugLogStartIndex);
            
            DB::nonQuery('UPDATE `_e_migrations` SET Timestamp = FROM_UNIXTIME(%u), Status = "%s" WHERE `Key` = "%s"', array($migration['executed'], $migration['status'], $migrationKey));
            
            return static::respond('migrationExecuted', array(
                'data' => $migration
                ,'log' => $log
                ,'output' => $output
            ));
        }

        return static::respond('migration', array(
            'data' => $migration
        ));
    }

    protected static function createMigrationsTable()
    {
        DB::nonQuery(
            'CREATE TABLE `_e_migrations` ('
                . '`Key` varchar(255) NOT NULL'
                . ',`SHA1` char(40) NOT NULL'
                . ',`Timestamp` timestamp NOT NULL'
                . ',`Status` enum("skipped","started","failed","executed") NOT NULL'
                . ',PRIMARY KEY (`Key`)'
            .')'
        );
    }


    // conveniance methods for use within migrations
    protected static function resetMigrationStatus($migrationKey)
    {
        DB::nonQuery('DELETE FROM `_e_migrations` WHERE `Key` = "%s"', $migrationKey);
    }

    protected static function tableExists($tableName)
    {
        return (boolean)DB::oneRecord('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s"', DB::escape($tableName));
    }

    protected static function columnExists($tableName, $columnName)
    {
        return (boolean)DB::oneRecord('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s" AND COLUMN_NAME = "%s"', DB::escape(array($tableName, $columnName)));
    }

    protected static function getColumn($tableName, $columnName)
    {
        return DB::oneRecord('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s" AND COLUMN_NAME = "%s"', DB::escape(array($tableName, $columnName)));
    }

    protected static function getColumnType($tableName, $columnName)
    {
        return DB::oneValue('SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s" AND COLUMN_NAME = "%s"', DB::escape(array($tableName, $columnName)));
    }
    
    protected static function getColumnKey($tableName, $columnName)
    {
        return DB::oneValue('SELECT COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s" AND COLUMN_NAME = "%s"', DB::escape(array($tableName, $columnName)));
    }
    
    protected static function getColumnIsNullable($tableName, $columnName)
    {
        return 'YES' == DB::oneValue('SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s" AND COLUMN_NAME = "%s"', DB::escape(array($tableName, $columnName)));
    }
}