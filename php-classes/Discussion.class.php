<?php



 class Discussion extends VersionedRecord
{

	// versioning configuration
	static public $historyTable = 'history_discussions';

	// ActiveRecord configuration
	static public $tableName = 'discussions';
	static public $singularNoun = 'discussion';
	static public $pluralNoun = 'discussions';
	
	// required for shared-table subclassing support
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__);

	static public $fields = array(
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
	
	static public $relationships = array(
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
	

	static public function getByHandle($handle)
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
		if(!$this->Handle)
		{
			$this->Handle = static::getUniqueHandle($this->Title);
		}
	
		// call parent
		parent::save();
	}


}