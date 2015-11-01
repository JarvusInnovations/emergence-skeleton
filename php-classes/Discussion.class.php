<?php



 class Discussion extends VersionedRecord
 {
     // versioning configuration
    public static $historyTable = 'history_discussions';

    // ActiveRecord configuration
    public static $tableName = 'discussions';
     public static $singularNoun = 'discussion';
     public static $pluralNoun = 'discussions';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
     public static $defaultClass = __CLASS__;
     public static $subClasses = array(__CLASS__);

     public static $fields = array(
        'ContextClass' => array(
            'type' => 'enum'
            ,'values' => array('Forum')
        )
        ,'Title'
        ,'Handle' => array(
            'unique' => true
        )
        ,'Status' => array(
            'type' => 'enum'
            ,'values' => array('Live','Deleted','Sticky')
            ,'default' => 'Live'
        )

        ,'Founded' => array(
            'type' => 'timestamp'
        )
        ,'FounderID' => array(
            'type' => 'integer'
            ,'unsigned' => true
        )

        ,'LastPost' => array(
            'type' => 'timestamp'
            ,'notnull' => false
        )
    );

     public static $relationships = array(
        'Comments' => array(
            'type' => 'context-children'
            ,'class' => 'Comment'
            ,'contextClass' => __CLASS__
            ,'order' => array('ID' => 'ASC')
        )
        ,'Founder' => array(
            'type' => 'one-one'
            ,'class' => 'User'
        )
    );


     public static function getByHandle($handle)
     {
         return static::getByField('Handle', $handle, true);
     }

     public function validate()
     {
         // call parent
        parent::validate();

         $this->_validator->validate(array(
            'field' => 'Title'
            ,'errorMessage' => 'Please enter a title for your discussion'
        ));

        // save results
        return $this->finishValidation();
     }

     public function save()
     {
         // set handle
        if (!$this->Handle) {
            $this->Handle = static::getUniqueHandle($this->Title);
        }

        // call parent
        parent::save();
     }
 }