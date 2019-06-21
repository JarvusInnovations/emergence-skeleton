<?php

namespace Emergence\Connectors;

class Mapping extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'connector_mappings';
    public static $singularNoun = 'connector mapping';
    public static $pluralNoun = 'connector mappings';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = [__CLASS__];

    public static $fields = [
        'ContextClass' => [
            'type' => 'string',
            'collate' => 'ascii_bin'
        ],
        'ContextID' => 'uint',
        'Source' => [
            'type' => 'enum',
            'values' => ['creation', 'matching', 'manual']
        ],
        'Connector' => [
            'type' => 'string',
            'length' => 25,
            'collate' => 'ascii_bin'
        ],
        'ExternalKey' => [
            'type' => 'string',
            'length' => 25,
            'collate' => 'ascii_bin'
        ],
        'ExternalIdentifier' => [
            'type' => 'string',
            'collate' => 'utf8_bin'
        ]
    ];

    public static $relationships = [
        'Context' => [
            'type' => 'context-parent'
        ]
    ];

    public static $indexes = [
        'Mapping' => [
            'fields' => ['Connector', 'ExternalKey', 'ExternalIdentifier'],
            'unique' => true
        ]
    ];

    public static function create($values = [], $save = false)
    {
        try {
            $Mapping = parent::create($values, $save);
        } catch (\DuplicateKeyException $e) {
            $Mapping = static::getByWhere([
                'ContextClass' => $values['Context'] ? $values['Context']->getRootClass() : $values['ContextClass'],
                'Connector' => $values['Connector'],
                'ExternalKey' => $values['ExternalKey'],
                'ExternalIdentifier' => $values['ExternalIdentifier']
            ]);

            $Mapping->ContextID = $values['Context'] ? $values['Context']->ID : $values['ContextID'];

            if ($save) {
                $Mapping->save();
            }
        }

        return $Mapping;
    }
}
