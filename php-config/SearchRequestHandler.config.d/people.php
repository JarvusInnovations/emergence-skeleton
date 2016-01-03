<?php

if ($GLOBALS['Session']->hasAccountLevel('User')) {
    SearchRequestHandler::$searchClasses[Emergence\People\User::class] = [
        'fields' => [
            [
                'field' => 'FirstName',
                'method' => 'like'
            ],
            [
                'field' => 'LastName',
                'method' => 'like'
            ],
            [
                'field' => 'Username',
                'method' => 'like'
            ]
        ],
        'conditions' => ['AccountLevel != "Deleted"']
    ];
}