<?php

class GroupMember extends ActiveRecord
{
    // ActiveRecord configuration
    static $tableName = 'group_members';
	
	static $fields = array(
		'GroupID' => array(
			'type' => 'integer'
			,'unsigned' => true
		)
		,'PersonID' => array(
    		'type' => 'integer'
			,'unsigned' => true
		)
		,'Expires' => array(
			'type' => 'timestamp'
			,'notnull' => false
		)
		,'Role' => array(
			'type' => 'enum'
			,'values' => array('Member','Administrator','Owner')
		)
		,'Rank' => array(
			'type' => 'integer'
			,'unsigned' => true
			,'notnull' => false
		)
        ,'Title' => array(
            'type' => 'string'
            ,'notnull' => false
        )
		,'Joined' => array(
			'type' => 'timestamp'
		)
	);

	static $relationships = array(
		'Person' => array(
			'type' => 'one-one'
			,'class' => 'Person'
			,'local' => 'PersonID'
		)
		,'Group' => array(
			'type' => 'one-one'
			,'class' => 'Group'
			,'local' => 'GroupID'
		)
	);
    
    static $indexes = array(
        'GroupPerson' => array(
            'fields' => array('GroupID','PersonID')
            ,'unique' => true
        )
    );
    
    public function getData()
	{
		return array_merge(parent::getData(), array(
			'Group' => $this->Group ? $this->Group : $this->Group->getData()
		));
	}
	
	public function save($deep = true)
	{
		if(!$this->Joined)
			$this->Joined = time();				
	
		// call parent
		parent::save($deep);
	}

}