<?php

Git::$repositories['Emergence-Skeleton'] = array(
    'remote' => 'git@github.com:JarvusInnovations/Emergence-Skeleton.git'
    ,'originBranch' => 'master'
    ,'workingBranch' => 'skeleton-dev.sites.emr.ge'
    ,'localOnly' => false
	,'trees' => array(
        'dwoo-plugins'
        ,'event-handlers'
#        ,'ext-library'
        ,'html-templates'
        ,'js-library'
        ,'php-classes'
        ,'php-config'
        ,'phpunit-tests'
        ,'php-migrations'
        ,'site-root'
        ,'sencha-workspace/.sencha'
        ,'sencha-workspace/microloaders'
        ,'sencha-workspace/pages'
        ,'sencha-workspace/packages/jarvus-apikit'
        ,'sencha-workspace/packages/jarvus-ext-actionevents'
        ,'sencha-workspace/packages/jarvus-ext-glyphs'
        ,'sencha-workspace/packages/jarvus-ext-lazydata'
        ,'sencha-workspace/packages/jarvus-ext-routing'
        ,'sencha-workspace/packages/jarvus-ext-uploadbox'
        ,'sencha-workspace/packages/emergence-apikit'
        ,'sencha-workspace/packages/emergence-cms'
        ,'sencha-workspace/packages/emergence-cms-summaries'
        ,'sencha-workspace/packages/emr-skeleton-theme'
        ,'sencha-workspace/EmergenceEditor'
        ,'sencha-workspace/EmergencePullTool'
        ,'sencha-workspace/ContentEditor'
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

#Git::$repositories['sencha-hotfixes'] = array(
#    'remote' => 'git@github.com:JarvusInnovations/sencha-hotfixes.git'
#    ,'originBranch' => 'master'
#    ,'workingBranch' => 'master'
#    ,'localOnly' => false
#    ,'trees' => array(
#        'sencha-workspace/packages/jarvus-hotfixes-ext-5.0.0.736' => 'jarvus-hotfixes-ext-5.0.0.736',
#        'sencha-workspace/packages/jarvus-hotfixes-ext-5.0.0.970' => 'jarvus-hotfixes-ext-5.0.0.970',
#        'sencha-workspace/packages/jarvus-hotfixes-ext-5.0.1.1255' => 'jarvus-hotfixes-ext-5.0.1.1255'
#	)
#);