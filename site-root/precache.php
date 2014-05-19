<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

$trees = array(
    'dwoo-plugins'
    ,'ext-library'
    ,'html-templates'
    ,'js-library'
    ,'php-classes'
    ,'php-config'
    ,'phpunit-tests'
    ,'site-root'
    ,'sencha-workspace/.sencha'
    ,'sencha-workspace/microloaders'
    ,'sencha-workspace/pages'
    ,'sencha-workspace/EmergenceEditor'
    ,'sencha-workspace/EmergencePullTool'
);

foreach ($trees AS $tree) {
    Debug::dumpVar(Emergence_FS::cacheTree($tree), false, "Cached tree: $tree");
}