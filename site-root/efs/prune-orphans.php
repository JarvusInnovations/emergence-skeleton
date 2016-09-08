<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

header('Content-Type: text/plain');

// delete orphan collections
DB::nonQuery('DELETE c3 FROM (SELECT c1.* FROM _e_file_collections c1 LEFT JOIN _e_file_collections c2 ON c2.ID = c1.ParentID WHERE c1.ParentID IS NOT NULL AND c2.ID IS NULL) orphan JOIN _e_file_collections c3 ON (c3.PosLeft BETWEEN orphan.PosLeft AND orphan.PosRight)');
printf("Deleted %u orphan collections\n", DB::affectedRows());

// delete orphan files
DB::nonQuery('DELETE f FROM _e_files f LEFT JOIN _e_file_collections c ON c.ID = f.CollectionID WHERE c.ID IS NULL');
printf("Deleted %u orphan files\n", DB::affectedRows());