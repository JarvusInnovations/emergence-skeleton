<?php

if (Site::getConfig('parent_hostname') == null) {
    Git::$repositories['emergence-skeleton-v1'] = [
        'remote' => 'https://github.com/JarvusInnovations/emergence-skeleton.git',
        'originBranch' => 'master',
        'workingBranch' => 'master',
        'trees' => [
            'api-docs',
            'data-exporters',
            'dwoo-plugins',
            'event-handlers',
            'html-templates',
            'php-classes',
            'php-config',
            'php-migrations',
            'phpunit-tests',
            'sencha-workspace',
            'site-root',
            'site-tasks',
            'webapp-builds'
        ]
    ];
}
