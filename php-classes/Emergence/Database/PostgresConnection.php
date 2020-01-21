<?php

namespace Emergence\Database;

use PDO;

class PostgresConnection extends AbstractSqlConnection
{
    protected static $defaultInstance;

    public static function createInstance($pdo = null)
    {
        $pdo = $pdo ?: [];

        if (is_array($pdo)) {
            $pdoConfig = $pdo;

            $dsn = 'pgsql:options=\'--client_encoding=UTF8\';dbname=' . $pdoConfig['database'];

            $dsn .= ';host=' . ($pdoConfig['host'] ?: 'localhost');
            $dsn .= ';port=' . ($pdoConfig['port'] ?: 5432);

            if (!empty($pdoConfig['application_name'])) {
                $dsn .= ';application_name=' . $pdoConfig['application_name'];
            }

            $pdo = new PDO($dsn, $pdoConfig['username'], $pdoConfig['password']);

            if (!empty($pdoConfig['search_path'])) {
                $pdo->query('SET search_path = "'.implode('","', $pdoConfig['search_path']).'"')->closeCursor();
            }
        }

        return parent::createInstance($pdo);
    }

    public function quoteValue($value)
    {
        return $value === null ? 'NULL' : $this->pdo->quote($value);
    }

    /*
    * Method below is STILL expiremental/in development.
    *
    */

    public function insertMultiple($table, array $rows = [])
    {
        $query = 'INSERT INTO ' . $this->quoteIdentifier($table);

        if (!empty($rows)) {
            $query .= ' (' . implode(',', array_map([$this, 'quoteIdentifier'], array_keys($rows[0]))) . ')';
            $query .= ' VALUES ';

            foreach (array_chunk($rows, 10000) as $chunkedRows) {
                $totalRows = count($chunkedRows);
                $statement = $query;
                for ($i = 0; $i < $totalRows; $i++) {
                    $statement .= '(' . implode(',', array_map([$this, 'quoteValue'], array_values($chunkedRows[$i]))) . ')';
                    if ($i + 1 < $totalRows) {
                        $statement .= ', ';
                    }
                }
                $this->nonQuery($statement);
            }
        } else {
            $query .= ' DEFAULT VALUES';
            $this->nonQuery($query);
        }
    }
}
