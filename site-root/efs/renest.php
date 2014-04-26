<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

set_time_limit(0);

$count = NestingBehavior::repairTable('_e_file_collections', 'PosLeft', 'PosRight');

die("Renested $count nodes");