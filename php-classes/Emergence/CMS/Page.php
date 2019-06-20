<?php

namespace Emergence\CMS;

use ActiveRecord;

class Page extends AbstractContent
{
    // ActiveRecord configuration
    public static $defaultClass = __CLASS__;
    public static $singularNoun = 'page';
    public static $pluralNoun = 'pages';
    public static $collectionRoute = '/pages';

    public static $fields = [
        'LayoutClass' => [
            'type' => 'enum'
            ,'values' => ['OneColumn']
            ,'default' => 'OneColumn'
        ]
        ,'LayoutConfig'  => 'json'
    ];


    public static function getAllPublishedByContextObject(ActiveRecord $Context, $options = [])
    {
        $options = array_merge([
            'conditions' => []
        ], $options);

        $options['conditions']['Class'] = __CLASS__;

        return parent::getAllPublishedByContextObject($Context, $options);
    }
}
