<?php

Git::$repositories['Emergence-Skeleton'] = array(
    'remote' => 'git@github.com:JarvusInnovations/Emergence-Skeleton.git'
    ,'originBranch' => 'master'
    ,'workingBranch' => 'skeleton-dev.sites.emr.ge'
	,'localOnly' => false
	,'trees' => array(
        'dwoo-plugins'
        ,'ext-library'
        ,'html-templates'
        ,'js-library'
        ,'php-classes'
        ,'php-config'
        ,'phpunit-tests'
        ,'site-root'
        ,'sencha-workspace'
	)
);