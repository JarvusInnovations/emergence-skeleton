<?php

class Category extends ActiveRecord
{
    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = array(__CLASS__);

    // configure ActiveRecord
    public static $tableName = 'categories';
    public static $singularNoun = 'category';
    public static $pluralNoun = 'categories';

    public static $fields = array(
        'Title'
        ,'Handle' => array(
            'unique' => true
        )
        ,'Description'
        ,'ParentID' => array(
            'type'  =>  'integer'
            ,'unsigned' => true
        )
        ,'Order' => array(
            'type'  =>  'integer'
            ,'unsigned' => true
        )
    );

    public static $relationships = array(
        'Creator' => array(
            'type' => 'one-one'
            ,'local' => 'CreatorID'
            ,'class' => 'Person'
        )
        ,'Parent' => array(
            'type'    => 'one-one'
            ,'local' => 'ParentID'
            ,'class' => __CLASS__
        )
    );

    public function getItems()
    {
        return static::instantiateRecords(DB::allRecords(sprintf("SELECT `Content`.* FROM `category_items` Link JOIN `content` Content ON (`Content`.`ID`=`Link`.`ContextID`) WHERE `Link`.`CategoryID`='%d'",$this->ID)));
    }

    public function getValue($name)
    {
        switch ($name) {
            case 'Items':
                return $this->getItems();
            default:
                return parent::getValue($name);
        }
    }

    public static function getByHandle($handle)
    {
        return static::getByField('Handle', $handle, true);
    }

    public function destroy()
    {

        // delete all CategoryItems

        return parent::destroy();
    }
    public static function delete($id)
    {
        DB::nonQuery('DELETE FROM `%s` WHERE CategoryID = %u', array(
            CategoryItem::$tableName,
            $id
        ));

        return parent::delete($id);
    }


    public function save($deep = true)
    {
        // set handle
        if (!$this->Handle) {
            $this->Handle = strtolower(static::getUniqueHandle($this->Title));
        }

        return parent::save($deep);
    }
}