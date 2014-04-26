<?php

namespace Migrations;

abstract class AbstractMigration
{
    static public $pretend = false;
    static public $printLog = true;
    
    static public function upgrade()
    {
        
    }
    
    static protected function addSql($sql, $params = array())
    {
        if (static::$printLog) {
            print(DB::prepareQuery($sql, $params).';'.PHP_EOL);
        }
        
        if (!static::$pretend) {
            DB::nonQuery($sql, $params);
        }
    }
}