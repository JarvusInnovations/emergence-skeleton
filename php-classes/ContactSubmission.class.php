<?php



 class ContactSubmission extends ActiveRecord
{

	// support subclassing
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);

	// ActiveRecord configuration
	static public $tableName = 'contact_submissions';
	static public $singularNoun = 'contact submission';
	static public $pluralNoun = 'contact submissions';

	
	static public $fields = array(
		'ContextClass' => null
		,'ContextID' => null
		,'Subform' => array(
			'type' => 'string'
			,'notnull' => false
		)
		,'Data' => 'serialized'
	);

}