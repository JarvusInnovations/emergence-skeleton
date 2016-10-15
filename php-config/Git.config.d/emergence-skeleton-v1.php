<?php

if (Site::getConfig('handle') == 'skeleton-v1') {
    Git::$repositories['emergence-skeleton-v1'] = [
        'remote' => 'https://github.com/JarvusInnovations/emergence-skeleton.git',
        'originBranch' => 'master',
        'workingBranch' => 'master',
        'trees' => [
            'dwoo-plugins',
            'event-handlers',
            'ext-library',
            'html-templates',
            'js-library',
            'php-classes',
            'php-config',
            'php-migrations',
            'phpunit-tests',
            'sencha-build',
            'sencha-workspace',
            'site-root'
        ]
    ];
}