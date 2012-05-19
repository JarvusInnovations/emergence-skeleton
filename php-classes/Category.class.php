<?php

class Category extends ActiveRecord
{

	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);

	// configure ActiveRecord
	static public $tableName = 'categories';
	static public $singularNoun = 'category';
	static public $pluralNoun = 'categories';
	
	static public $fields = array(
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
	
	static public $relationships = array(
		'Creator' => array(
			'type' => 'one-one'
			,'local' => 'CreatorID'
			,'class' => 'Person'
		)
		,'Parent' => array(
			'type'	=> 'one-one'
			,'local' => 'ParentID'
			,'class' => __CLASS__
		)
	);
	
	public function getItems() {
		
		return static::instantiateRecords(DB::allRecords(sprintf("SELECT `Content`.* FROM `category_items` Link JOIN `content` Content ON (`Content`.`ID`=`Link`.`ContextID`) WHERE `Link`.`CategoryID`='%d'",$this->ID)));
	}
	
	public function getValue($name)
	{
		switch($name) {
			case 'Items':
				return $this->getItems();
			default:
				return parent::getValue($name);
		}	
	}
	
	static public function getByHandle($handle)
	{
		return static::getByField('Handle', $handle, true);
	}
	
    public function destroy() {
    	
    	// delete all CategoryItems
    	DB::nonQuery("DELETE FROM `" . CategoryItem::$tableName . "` WHERE `CategoryID`='" . $this->ID ."'");
    	
    	return parent::destroy();
    }
	
	public function save()
	{
		// set handle
		if(!$this->Handle)
			$this->Handle = strtolower(static::getUniqueHandle($this->Title));
		
		return parent::save(true);
	}
}