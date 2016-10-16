<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

set_time_limit(0);

header('Content-Type: text/plain');


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

printf("Found %u empty collections\n", count($emptyCollectionIds));

if (!count($emptyCollectionIds)) {
    exit();
}


// delete empty collections
DB::nonQuery('DELETE FROM _e_file_collections WHERE ID IN (%s)', implode(',', $emptyCollectionIds));
printf("Deleted %u empty collections\n", DB::affectedRows());


$count = NestingBehavior::repairTable('_e_file_collections', 'PosLeft', 'PosRight');
printf("Renested %u remaining collections\n", $count);