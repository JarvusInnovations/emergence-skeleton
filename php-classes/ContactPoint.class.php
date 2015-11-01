<?php



 class ContactPoint extends VersionedRecord
 {
     // VersionedRecord configuration
    public static $historyTable = 'history_contact_points';

    // ActiveRecord configuration
    public static $tableName = 'contact_points';
     public static $singularNoun = 'contact point';
     public static $pluralNoun = 'contact points';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
     public static $defaultClass = __CLASS__;
     public static $subClasses = array('EmailContactPoint','PhoneContactPoint','AddressContactPoint','NetworkContactPoint','LinkContactPoint');

     public static $fields = array(
        'ContextClass' => null    // delete these two lines to
        ,'ContextID' => null    // enable the context relationship

        ,'PersonID' => array(
            'type' => 'integer'
            ,'unsigned' => true
            ,'index' => true
        )
        ,'Label' => array(
            'type' => 'string'
            ,'notnull' => false
        )

        ,'Data' => array(
            'type' => 'serialized'
        )
    );


     public static $relationships = array(
        'Person' => array(
            'type' => 'one-one'
            ,'class' => 'Person'
        )
    );


     public static function getByPerson(Person $Person, $conditions = array())
     {
         $conditions['PersonID'] = $Person->ID;

         return static::getByWhere($conditions);
     }


     public static function getByLabel(Person $Person, $label)
     {
         return static::getByWhere(array(
            'PersonID' => $Person->ID
            ,'Label' => $label
        ));
     }

     public static function getByClass(Person $Person, $class = false)
     {
         return static::getByWhere(array(
            'PersonID' => $Person->ID
            ,'Class' => $class ? $class : get_called_class()
        ));
     }


     public function validate($deep = true)
     {
         // call parent
        parent::validate($deep);


        // save results
        return $this->finishValidation();
     }

     public function toString()
     {
         return (string)$this->Data;
     }
 }