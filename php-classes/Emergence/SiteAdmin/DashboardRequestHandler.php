<?php

namespace Emergence\SiteAdmin;

use Person;
use User;


class DashboardRequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Administrator');

        return static::respond('dashboard', [
            'metrics' => [
                [
                    'label' => 'People',
                    'value' => Person::getCount(),
                    'link' => '/people'
                ],
                [
                    'label' => 'Users',
                    'value' => User::getCount(['Username IS NOT NULL']),
                    'link' => '/people?q=class:User'
                ],
                [
                    'label' => 'Administrators',
                    'value' => User::getCount(['AccountLevel' => 'Administrator']),
                    'link' => '/people?q=accountlevel:Administrator'
                ],
                [
                    'label' => 'Developers',
                    'value' => User::getCount(['AccountLevel' => 'Developer']),
                    'link' => '/people?q=accountlevel:Developer'
                ]
            ]
        ]);
    }
}