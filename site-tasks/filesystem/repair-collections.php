<?php

return [
    'title' => 'Repair collections tree',
    'description' => 'Repairs and cleans collections tree by removing entries that have never had files, remapping left/right nested set fields, and clearing efs data from the site cache',
    'warning' => 'This operation could render the site unresponsive if executed during high load',
    'icon' => 'wrench',
    'requireAccountLevel' => 'Developer',
    'handler' => function ($taskConfig) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            set_time_limit(0);

            // get IDs of collections with no descendent files
            $emptyCollectionIds = DB::allValues(
                'ID',
                '  SELECT c1.ID'
                .'   FROM _e_file_collections c1'
                .'   JOIN _e_file_collections c2 ON c2.PosLeft BETWEEN c1.PosLeft AND c1.PosRight'
                .'   JOIN (SELECT c.ID, COUNT(f.ID) AS TotalFiles FROM _e_file_collections c LEFT JOIN _e_files f ON f.CollectionID = c.ID GROUP BY c.ID) _col_files ON _col_files.ID = c2.ID'
                .'  GROUP BY c1.ID'
                .' HAVING SUM(_col_files.TotalFiles) = 0'
            );

            // delete empty collections
            if (count($emptyCollectionIds)) {
                DB::nonQuery('DELETE FROM _e_file_collections WHERE ID IN (%s)', implode(',', $emptyCollectionIds));
                $erasedCollections = DB::affectedRows();
            } else {
                $erasedCollections = 0;
            }

            // renest all collections
            $renestedCount = NestingBehavior::repairTable('_e_file_collections', 'PosLeft', 'PosRight');

            // clear EFS cache
            $keysDeleted = Cache::deleteByPattern('/^efs:/');

            return static::respond('message', [
                'title' => 'Collections repaired',
                'message' => "Erased $erasedCollections unused collections, renested $renestedCount collections, and cleared $keysDeleted cache entries"
            ]);
        }

        return static::respond('confirm', [
            'question' => 'Repair collections tree?'
        ]);
    }
];