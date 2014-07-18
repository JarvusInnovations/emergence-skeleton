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
        ,'php-migrations'
        ,'site-root'
        ,'sencha-workspace'
	)
);

Git::$repositories['ext-library'] = array(
    'remote' => 'git@github.com:JarvusInnovations/ext-library.git'
    ,'originBranch' => 'master'
    ,'workingBranch' => 'skeleton-dev.sites.emr.ge'
    ,'localOnly' => false
	,'trees' => array(
        'ext-library/Emergence/ext' => 'Emergence/ext'
        ,'ext-library/Emergence/touch' => 'Emergence/touch'
        ,'ext-library/Emergence/util' => 'Emergence/util'
        ,'ext-library/Jarvus/ext' => 'Jarvus/ext'
        ,'ext-library/Jarvus/touch' => 'Jarvus/touch'
        ,'ext-library/Jarvus/util' => 'Jarvus/util'
	)
);