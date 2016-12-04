<?php

return [
    'title' => 'Manage application cache',
    'description' => 'Browse and selective clear application cache entries',
    'icon' => 'pencil',
    'requireAccountLevel' => 'Developer',
    'handler' => function () {
        $prefixLength = strlen(Cache::getKeyPrefix());
        $entries = [];

        foreach (Cache::getIterator('/.*/') AS $key => $entry) {
            $key = substr($key, $prefixLength);
            $entries[$key] = [
                'key' => $key,
                'hits' => $entry['num_hits'],
                'size' => $entry['mem_size'],
                'accessTime' => $entry['access_time'],
                'createTime' => $entry['creation_time'],
                'modifyTime' => $entry['mtime']
            ];
        }

        uasort($entries, function ($a, $b) {
            if ($a['hits'] == $b['hits']) {
                return 0;
            }

            return $a['hits'] > $b['hits'] ? -1 : 1;
        });

        return static::respond('manage', [
            'entries' => $entries
        ]);
    }
];