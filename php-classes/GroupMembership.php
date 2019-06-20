<?php



class GroupMembership extends ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'group_memberships';

    public static $fields = [
        'PersonID' => [
            'type' => 'integer'
            ,'unsigned' => true
        ]
        ,'GroupID' => [
            'type' => 'integer'
            ,'unsigned' => true
        ]
        ,'Expires' => [
            'type' => 'timestamp'
            ,'notnull' => false
        ]
        ,'Type' => [
            'type' => 'enum'
            ,'values' => ['Member','Administrator','Owner']
        ]
        ,'Rank' => [
            'type' => 'integer'
            ,'unsigned' => true
            ,'notnull' => false
        ]
    ];

    public static $relationships = [
        'Person' => [
            'type' => 'one-one'
            ,'class' => 'Person'
            ,'local' => 'PersonID'
        ]
        ,'Group' => [
            'type' => 'one-one'
            ,'class' => 'Person'
            ,'local' => 'PersonID'
        ]
    ];
}
