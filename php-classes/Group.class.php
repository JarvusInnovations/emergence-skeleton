<?php

class Group extends ActiveRecord
{
    // ActiveRecord configuration
    static public $tableName = 'groups';
    static public $singularNoun = 'group';
	static public $pluralNoun = 'groups';
	
	// the lowest-level class in your table requires these lines,
	// they can be manipulated via config files to plug in same-table subclasses
	static public $rootClass = __CLASS__;
	static public $defaultClass = __CLASS__;
	static public $subClasses = array(__CLASS__, 'Organization');

	static public $fields = array(
		'Name'
        ,'Handle' => array(
            'unique' => true
        )
		,'ParentID' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'Left' => array(
			'type' => 'uint'
			,'notnull' => false
            ,'unique' => true
		)
		,'Right' => array(
			'type' => 'uint'
			,'notnull' => false
		)
        ,'Founded' => array(
            'type' => 'timestamp'
        )
		,'Data' => array(
			'type' => 'json'
			,'notnull' => false
		)
		,'Status' => array(
			'type' => 'enum'
			,'values' => array('Active', 'Disabled')
			,'default' => 'Active'
		)
	);
	
	static public $relationships = array(
    	'Members' => array(
			'type' => 'one-many'
			,'class' => 'GroupMember'
		)
        ,'Parent' => array(
			'type' => 'one-one'
			,'class' => __CLASS__
		)
        ,'People' => array(
            'type' => 'many-many'
            ,'class' => 'Person'
            ,'linkClass' => 'GroupMember'
        )
	);
    
    static public $dynamicFields = array(
        'FullPath' => array(
            'method' => 'getFullPath'
        )
        ,'Population' => array(
            'method' => 'getPopulation'
        )
    );
    
    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);
        
        $this->_validator->validate(array(
            'field' => 'Name'
            ,'errorMessage' => 'A name is required'
        ));
        
        // implement handles
        HandleBehavior::onValidate($this, $this->_validator);                           
        
        // save results
        return $this->finishValidation();
    }
    
    public function save($deep = true, $createRevision = true)
    {
        // set webmaster
        if(!$this->Founded)
        {
            $this->Founded = time();
        }
        
        // implement handles
        HandleBehavior::onSave($this, strtolower($this->Name));                                
        
        // implement nesting
        NestingBehavior::onSave($this);
        
        // call parent
        parent::save($deep, $createRevision);
    }
    
    public function destroy()
    {
    	parent::destroy();
    	
    	NestingBehavior::onDestroy($this);
    }
    
    public function getAllPeople()
    {
        $order = PeopleRequestHandler::$browseOrder ? Person::mapFieldOrder(PeopleRequestHandler::$browseOrder) : array();
        
        array_unshift($order, 'GroupMember.Rank DESC');
        
        return Person::getAllByQuery(
            'SELECT Person.*'
            .' FROM `%s` GroupMember'
            .' JOIN `%s` Person ON(Person.ID = GroupMember.PersonID)'
            .' WHERE GroupMember.GroupID IN (SELECT ID FROM `%s` WHERE `Left` BETWEEN %u AND %u)'
            .' ORDER BY '.join(',', $order)
            ,array(
                GroupMember::$tableName
                ,Person::$tableName
                ,Group::$tableName
                ,$this->Left
                ,$this->Right
            )
        );
    }
    
    public function getFullPath()
    {
        return DB::oneValue(
    		'SELECT GROUP_CONCAT(Name SEPARATOR	"/") FROM `%s` WHERE `Left`<=%u AND `Right`>=%u ORDER BY `Left`'
			,array(
				static::$tableName
				,$this->Left
				,$this->Right
			)
		);
    }
    
    public function getPopulation()
    {
		try {
    		return DB::oneValue(
    			'SELECT COUNT(*) FROM (SELECT ID FROM `%s` WHERE `Left` BETWEEN %u AND %u) `Group` JOIN `%s` GroupMember ON GroupID = `Group`.ID'
    			,array(
            		static::$tableName
    				,$this->Left
    				,$this->Right
    				,GroupMember::$tableName
    			)
    		);
		} catch(TableNotFoundException $e) {
    	    return 0;   
		}
    }
    
    static public function setPersonGroups(Person $Person, $groupIDs)
    {
    	$assignedGroups = array();
		
		if (is_string($groupIDs)) {
			$groupIDs = preg_split('/\s*[,]+\s*/', trim($groupIDs));
		}
		
		foreach ($groupIDs AS $groupID) {
			if (!$groupID) {
				continue;
			}

			if ($Group = static::getByHandle($groupID)) {
				$Group->assignMember($Person);
				$assignedGroups[] = $Group->ID;
			}
		}

		// delete tags
		DB::query(
			'DELETE FROM `%s` WHERE PersonID = %u AND GroupID NOT IN (%s)'
			,array(
				GroupMember::$tableName
				,$Person->ID
				,count($assignedGroups) ? join(',', $assignedGroups) : '0'
			)
		);

		return $assignedGroups;
    }
    
    public function assignMember(Person $Person, $role = 'Member')
	{
		$memberData = array(
			'GroupID' => $this->ID
			,'PersonID' => $Person->ID
			,'Role' => $role
		);

		try {
			return GroupMember::create($memberData, true);
		} catch(DuplicateKeyException $e) {
			return GroupMember::getByWhere($memberData);
		}
	}
}