<?php


class EmailSubscriber extends ActiveRecord
{
    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = array(__CLASS__);

    // ActiveRecord configuration
    public static $tableName = 'email_subscribers';
    public static $singularNoun = 'email_subscriber';
    public static $pluralNoun = 'email subscribers';

    public static $fields = array(
        'ContextClass' => null
        ,'ContextID' => null
        ,'Name' => array(
            'type' => 'string'
            ,'notnull' => false
        )
        ,'Email' => array(
            'type' => 'string'
            ,'unique' => true
            ,'notnull' => true
        )
    );

    public function validate()
    {
        // call parent
        parent::validate();

        $this->_validator->validate(array(
            'field' => 'Email'
            ,'validator' => 'email'
        ));



        // save results
        return $this->finishValidation();
    }
}