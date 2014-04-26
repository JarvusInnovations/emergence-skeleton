<?php



 class ContactPoint extends VersionedRecord
{

	// VersionedRecord configuration
	static public $historyTable = 'history_contact_points';

	// ActiveRecord configuration
	static public $tableName = 'contact_points';
	static public $singularNoun = 'contact point';
	static public $pluralNoun = 'contact points';
	
	// required for shared-table subclassing support
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array('EmailContactPoint','PhoneContactPoint','AddressContactPoint','NetworkContactPoint','LinkContactPoint');

	static public $fields = array(
		'ContextClass' => null	// delete these two lines to
		,'ContextID' => null	// enable the context relationship
		
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
	
	
	static public $relationships = array(
		'Person' => array(
			'type' => 'one-one'
			,'class' => 'Person'
		)
	);
	
	
	static public function getByPerson(Person $Person, $conditions = array())
	{
		$conditions['PersonID'] = $Person->ID;
	
		return static::getByWhere($conditions);
	}

	
	static public function getByLabel(Person $Person, $label)
	{
		return static::getByWhere(array(
			'PersonID' => $Person->ID
			,'Label' => $label
		));
	}

	static public function getByClass(Person $Person, $class = false)
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