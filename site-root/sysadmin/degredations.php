<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

$config = Site::getConfig();

Debug::dump($config, false, 'current config');

// set a degredation
if (is_array($_REQUEST['degredations'])) {
    if (!is_array($config['degredations'])) {
        $config['degredations'] = array();
    }

    foreach ($_REQUEST['degredations'] AS $degredation => $value) {
        $config['degredations'][$degredation] = $value == 'on';
    }

    Debug::dump($config['degredations'], false, 'updated degredations map');

    // save to config cache for all hostnames
    $hostnames = $config['hostnames'];
    array_unshift($hostnames, $config['primary_hostname']);

    foreach ($hostnames AS $hostname) {
        apc_store($hostname, $config);
    }

    Debug::dump($hostnames, false, 'saved new degredations to hostnames');

    Debug::dump($config, false, 'final config');
}
