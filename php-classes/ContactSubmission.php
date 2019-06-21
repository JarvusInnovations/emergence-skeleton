<?php



class ContactSubmission extends ActiveRecord
{
    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = [__CLASS__];

    // ActiveRecord configuration
    public static $tableName = 'contact_submissions';
    public static $singularNoun = 'contact submission';
    public static $pluralNoun = 'contact submissions';


    public static $fields = [
        'ContextClass' => null,
        'ContextID' => null,
        'Subform' => [
            'type' => 'string',
            'notnull' => false
        ],
        'Data' => 'serialized'
    ];
}
