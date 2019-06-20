<?php

class SearchRequestHandler extends RequestHandler
{
    public static $searchClasses = [];
    public static $useBoolean = true;

    public static $userResponseModes = [
        'application/json' => 'json'
        ,'text/csv' => 'csv'
    ];

    public static function __classLoaded()
    {
        uasort(static::$searchClasses, function ($a, $b) {
            $a = !empty($a['weight']) ? $a['weight'] : 0;
            $b = !empty($b['weight']) ? $b['weight'] : 0;

            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
    }

    public static function handleRequest()
    {
        if (static::peekPath() == 'json') {
            static::$responseMode = static::shiftPath();
        }

        return static::handleSearchRequest();
    }

    public static function handleSearchRequest()
    {
        if (empty($_REQUEST['q'])) {
            return static::throwError('You did not supply any search terms');
        }

        if (!empty($_REQUEST['tag'])) {
            if (!$Tag = Tag::getByHandle($_REQUEST['tag'])) {
                return static::throwNotFoundError('Tag does not exist');
            }
        }

        if (empty(static::$searchClasses)) {
            return static::throwError('No search classes configured for this site');
        }

        $searchResults = [];
        $totalResults = 0;
        /*

        // Extra feature. Specify which classes to search for in Request parameter 'searchClasses'

        if(!empty($_REQUEST['searchClasses']))
        {
            $classes = explode(',', $_REQUEST['searchClasses']);
            foreach(static::$searchClasses AS $className => $options)
            {
                if(!in_array($className,$classes))
                    unset(static::$searchClasses[$className]);
            }

        }
        */
        foreach (static::$searchClasses as $className => $options) {
            if (is_string($options)) {
                $className = $options;
                $options = [];
            }

            $options = array_merge([
                'className' => $className
                ,'fields' => ['Title']
                ,'conditions' => []
            ], $options);

            if (empty($options['fields'])) {
                continue;
            }

            // parse fields
            $columns = [
                'fulltext' => []
                ,'like' => []
                ,'exact' => []
                ,'sql' => []
            ];
            foreach ($options['fields'] as $field) {
                // transform string-only
                if (is_string($field)) {
                    $field = [
                        'field' => $field
                    ];
                }

                // apply defaults
                $field = array_merge([
                    'method' => 'fulltext'
                ], $field);

                // sort conditions
                $columns[$field['method']][] = $field['method'] == 'sql' ? $field['sql'] : $className::getColumnName($field['field']);
            }

            // add match conditions
            $query = $_REQUEST['q'];
            $escapedQuery = DB::escape($query);
            $matchConditions = [];

            if ($columns['fulltext']) {
                $matchConditions[] = sprintf('MATCH (`%s`) AGAINST ("%s" %s)', implode('`,`', $columns['fulltext']), $escapedQuery, static::$useBoolean ? 'IN BOOLEAN MODE' : '');
            }

            if ($columns['like']) {
                $matchConditions[] =
                    '('
                    .join(') OR (', array_map(function ($column) use ($escapedQuery) {
                        return sprintf('`%s` LIKE "%%%s%%"', $column, $escapedQuery);
                    }, $columns['like']))
                    .')';
            }

            if ($columns['exact']) {
                $matchConditions[] =
                    '('
                    .join(') OR (', array_map(function ($column) use ($escapedQuery) {
                        return sprintf('`%s` = "%s"', $column, $escapedQuery);
                    }, $columns['exact']))
                    .')';
            }

            if ($columns['sql']) {
                $matchConditions[] =
                    '('
                    .join(') OR (', array_map(function ($sql) use ($query, $escapedQuery) {
                        return is_callable($sql) ? call_user_func($sql, $query) : sprintf($sql, $escapedQuery);
                    }, $columns['sql']))
                    .')';
            }


            $options['conditions'][] = join(' OR ', $matchConditions);

            $tableAlias = $className::getTableAlias();
            try {
                if (isset($Tag)) {
                    $results = DB::allRecords(
                        'SELECT %s.*'
                        .' FROM `tag_items` t'
                        .' INNER JOIN `%s` p ON (p.ID = t.`ContextID`)'
                        .' WHERE t.`TagID` = %u AND t.`ContextClass` = "%s"'
                        .' AND (%s)',
                        [
                            $tableAlias,
                            $className::$tableName,
                            $tableAlias,
                            $Tag->ID,
                            $className,
                            join(') AND (', $className::mapConditions($options['conditions']))
                        ]
                    );
                } else {
                    $results = DB::allRecords(
                        'SELECT * FROM `%s` %s WHERE (%s)',
                        [
                            $className::$tableName,
                            $tableAlias,
                            join(') AND (', $className::mapConditions($options['conditions']))
                        ]
                    );
                }
            } catch (TableNotFoundException $e) {
                $results = [];
            }

            $classResults = count($results);
            $totalResults += $classResults;

            $searchResults[$className] = $classResults ? ActiveRecord::instantiateRecords($results) : [];
        }

        //DebugLog::dumpLog();

        static::respond('search', [
            'data' => $searchResults
            ,'totalResults' => $totalResults
        ]);
    }
}
