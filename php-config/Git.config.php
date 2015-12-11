<?php

Git::$repositories['emergence-skeleton'] = array(
    'remote' => 'https://github.com/JarvusInnovations/emergence-skeleton.git',
    'originBranch' => 'master',
    'workingBranch' => 'master',
    'trees' => array(
        'dwoo-plugins',
        'event-handlers',
        'html-templates',
        'js-library',
        'php-classes',
        'php-config',
        'phpunit-tests',
        'php-migrations',
        'site-root',
        'sencha-workspace/.sencha',
        'sencha-workspace/microloaders',
        'sencha-workspace/pages',
        'sencha-workspace/packages/emergence-cms',
        'sencha-workspace/packages/emergence-cms-summaries',
        'sencha-workspace/packages/emr-skeleton-theme',
        'sencha-workspace/EmergenceEditor',
        'sencha-workspace/EmergencePullTool',
        'sencha-workspace/ContentEditor'
    )
);

Git::$repositories['jarvus-apikit'] = array(
    'remote' => 'https://github.com/JarvusInnovations/jarvus-apikit.git',
    'originBranch' => 'master',
    'workingBranch' => 'master',
    'trees' => array(
        'sencha-workspace/packages/jarvus-apikit' => '.'
    )
);

Git::$repositories['emergence-apikit'] = array(
    'remote' => 'https://github.com/JarvusInnovations/emergence-apikit.git',
    'originBranch' => 'master',
    'workingBranch' => 'master',
    'trees' => array(
        'sencha-workspace/packages/emergence-apikit' => '.'
    )
);

Git::$repositories['jarvus-fileupload'] = array(
    'remote' => 'https://github.com/JarvusInnovations/jarvus-fileupload.git',
    'originBranch' => 'master',
    'workingBranch' => 'master',
    'trees' => array(
        'sencha-workspace/packages/jarvus-fileupload' => '.'
    )
);