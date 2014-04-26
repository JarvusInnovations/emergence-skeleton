<?php

namespace Migrations;

use DB;

abstract class AbstractMigration
{
    static public $pretend = false;
    static public $printLog = true;
    static public $continueOnException = false;
    
    static public function upgrade()
    {
        
    }
    
    static protected function addSql($sql, $params = array())
    {
        if (static::$printLog) {
            print(DB::prepareQuery($sql, $params).';'.PHP_EOL);
        }
        
        if (!static::$pretend) {
            try {
                DB::nonQuery($sql, $params);
            } catch (\Exception $e) {
                if (static::$continueOnException) {
                    if (static::$printLog) {
                        printf("\t^ LAST QUERY FAILED: %s(%s, %u)\n", get_class($e), $e->getMessage(), $e->getCode());
                    }
                } else {
                    throw $e;
                }
            }
        }
    }
}