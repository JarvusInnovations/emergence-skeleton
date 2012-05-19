<?php



 class Group extends ActiveRecord
{

	// ActiveRecord configuration
	static $tableName = 'groups';
	
	static $fields = array(
		'Title'
		,'Handle'
	);
	
	static $relationships = array(
		'GroupMemberships' => array(
			'type' => 'one-many'
			,'class' => 'GroupMembership'
			,'indexField' => 'GroupID'
			,'foreign' => 'GroupID'
		)
	);


	static public function getByHandle($sessionHandle)
	{
		return static::getByField('Handle', $sessionHandle, true);
	}

	
	public function getMembers($options = array())
	{
		$options = Site::prepareOptions($options, array(
			'conditions' => array()
			,'order' => 'Rank IS NULL, Rank'
		));
		
		$options['conditions']['GroupID'] = $this->ID;
		
		return GroupMembership::getAllByWhere($options['conditions'], $options);
	}


}