<?php

class Person extends VersionedRecord implements Emergence\People\IPerson
{
    public static $classLabel = 'Person / Contact';

    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = array(__CLASS__);

    // ActiveRecord configuration
    public static $tableName = 'people';
    public static $singularNoun = 'person';
    public static $pluralNoun = 'people';
    public static $collectionRoute = '/people';

    public static $existingEmailError = 'Email already registered to another account.';

    public static $fields = array(
        'FirstName' => array(
            'includeInSummary' => true
        )
        ,'LastName' => array(
            'includeInSummary' => true
        )
        ,'MiddleName' => array(
            'notnull' => false
        )
        ,'Gender' => array(
            'type' => 'enum'
            ,'values' => array('Male','Female')
            ,'notnull' => false
        )
        ,'BirthDate' => array(
            'type' => 'date'
            ,'notnull' => false
            ,'accountLevelEnumerate' => 'Staff'
        )
        ,'Email' => array(
            'notnull' => false
            ,'unique' => true
            ,'accountLevelEnumerate' => 'User'
        )
        ,'Phone' => array(
            'type' => 'decimal'
            ,'length' => '10,0'
            ,'unsigned' => true
            ,'notnull' => false
            ,'accountLevelEnumerate' => 'User'
        )
        ,'Location' => array(
            'notnull' => false
        )
        ,'About' => array(
            'type' => 'clob'
            ,'notnull' => false
        )
        ,'PrimaryPhotoID' => array(
            'type' => 'integer'
            ,'unsigned' => true
            ,'notnull' => false
        )
    );

    public static $relationships = array(
        'GroupMemberships' => array(
            'type' => 'one-many'
            ,'class' => 'Emergence\People\Groups\GroupMember'
            ,'indexField' => 'GroupID'
            ,'foreign' => 'PersonID'
        )
        ,'Notes' => array(
            'type' => 'context-children'
            ,'class' => 'Note'
            ,'contextClass' => 'Person'
            ,'order' => array('ID' => 'DESC')
        )
        ,'Groups' => array(
            'type' => 'many-many'
            ,'class' => 'Emergence\People\Groups\Group'
            ,'linkClass' => 'Emergence\People\Groups\GroupMember'
            ,'linkLocal' => 'PersonID'
            ,'linkForeign' => 'GroupID'
        )
        ,'PrimaryPhoto' => array(
            'type' => 'one-one'
            ,'class' => 'PhotoMedia'
            ,'local' => 'PrimaryPhotoID'
        )
        ,'Photos' => array(
            'type' => 'context-children'
            ,'class' => 'PhotoMedia'
            ,'contextClass' => __CLASS__
        )
        ,'Comments' => array(
            'type' => 'context-children'
            ,'class' => 'Comment'
            ,'contextClass' => __CLASS__
            ,'order' => array('ID' => 'DESC')
        )
    );

    public static $dynamicFields = array(
        'PrimaryPhoto'
        ,'Photos'
        ,'groupIDs' => array(
            'method' => 'getGroupIDs'
        )
    );

    public static $searchConditions = array(
        'FirstName' => array(
            'qualifiers' => array('any','name','fname','firstname','first')
            ,'points' => 2
            ,'sql' => 'FirstName LIKE "%%%s%%"'
        )
        ,'LastName' => array(
            'qualifiers' => array('any','name','lname','lastname','last')
            ,'points' => 2
            ,'sql' => 'LastName LIKE "%%%s%%"'
        )
        ,'Username' => array(
            'qualifiers' => array('any','username','uname','user')
            ,'points' => 2
            ,'sql' => 'Username LIKE "%%%s%%"'
        )
        ,'Gender' => array(
            'qualifiers' => array('gender','sex')
            ,'points' => 2
            ,'sql' => 'Gender LIKE "%s"'
        )
        ,'Class' => array(
            'qualifiers' => array('class')
            ,'points' => 2
            ,'sql' => 'Class LIKE "%%%s%%"'
        )
        ,'AccountLevel' => array(
            'qualifiers' => array('accountlevel')
            ,'points' => 2
            ,'sql' => 'AccountLevel LIKE "%%%s%%"'
        )
        ,'Group' => array(
            'qualifiers' => array('group')
            ,'points' => 1
            ,'join' => array(
                'className' => 'Emergence\People\Groups\GroupMember'
                ,'aliasName' => 'Emergence\People\Groups\GroupMember'
                ,'localField' => 'ID'
                ,'foreignField' => 'PersonID'
            )
            ,'callback' => 'getGroupConditions'
        )
    );

    // Person
    public static $requireEmail = false;
    public static $requirePhone = false;
    public static $requireGender = false;
    public static $requireBirthDate = false;
    public static $requireLocation = false;
    public static $requireAbout = false;


    public function getValue($name)
    {
        switch ($name) {
            // name variations
            case 'FullName':
            {
                return $this->getFullName();
            }

            case 'FirstInitial':
            {
                return strtoupper(substr($this->FirstName, 0, 1));
            }

            case 'LastInitial':
            {
                return strtoupper(substr($this->LastName, 0, 1));
            }

            case 'FirstNamePossessive':
            {
                if (substr($this->FirstName, -1) == 's') {
                    return $this->FirstName.'\'';
                } else {
                    return $this->FirstName.'\'s';
                }
            }

            case 'FullNamePossessive':
            {
                $fullName = $this->FullName;

                if (substr($fullName, -1) == 's') {
                    return $fullName.'\'';
                } else {
                    return $fullName.'\'s';
                }
            }

            case 'EmailRecipient':
            {
                return sprintf('"%s" <%s>', $this->FullName, $this->Email);
            }

            default: return parent::getValue($name);
        }
    }

    public function getTitle()
    {
        return $this->getFullName();
    }

    public function getFullName()
    {
        return $this->FirstName.' '.$this->LastName;
    }

    public static function getByEmail($email)
    {
        return static::getByField('Email', $email);
    }

    public static function getByFullName($firstName, $lastName)
    {
        return static::getByWhere(array(
            'FirstName' => $firstName
            ,'LastName' => $lastName
        ));
    }

    public static function getOrCreateByFullName($firstName, $lastName, $save = false)
    {
        if ($Person = static::getByFullName($firstName, $lastName)) {
            return $Person;
        } else {
            return static::create(array(
                'FirstName' => $firstName
                ,'LastName' => $lastName
            ), $save);
        }
    }

    public static function parseFullName($fullName)
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        if (count($parts) != 2) {
            throw new Exception('Full name must contain a first and last name separated by a space.');
        }

        return array(
            'FirstName' => $parts[0]
            ,'LastName' => $parts[1]
        );
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        // strip any non-digit characters from phone before validation
        if ($this->Phone) {
            $this->Phone = preg_replace('/\D/', '', $this->Phone);
        }

        $this->_validator->validate(array(
            'field' => 'Class'
            ,'validator' => 'selection'
            ,'choices' => self::$subClasses
            ,'required' => false
        ));

        $this->_validator->validate(array(
            'field' => 'FirstName'
            ,'minlength' => 2
            ,'required' => true
            ,'errorMessage' => 'First name is required.'
        ));

        $this->_validator->validate(array(
            'field' => 'LastName'
            ,'minlength' => 2
            ,'required' => true
            ,'errorMessage' => 'Last name is required.'
        ));

        $this->_validator->validate(array(
            'field' => 'Email'
            ,'validator' => 'email'
            ,'required' => static::$requireEmail
        ));

        // check handle uniqueness
        if ($this->isDirty && !$this->_validator->hasErrors('Email') && $this->Email) {
            $ExistingUser = User::getByField('Email', $this->Email);

            if ($ExistingUser && ($ExistingUser->ID != $this->ID)) {
                $this->_validator->addError('Email', static::$existingEmailError);
            }
        }

        $this->_validator->validate(array(
            'field' => 'Phone'
            ,'validator' => 'phone'
            ,'required' => static::$requirePhone
        ));

        $this->_validator->validate(array(
            'field' => 'BirthDate'
            ,'validator' => 'date_ymd'
            ,'required' => static::$requireBirthDate
        ));

        $this->_validator->validate(array(
            'field' => 'Gender'
            ,'validator' => 'selection'
            ,'required' => static::$requireGender
            ,'choices' => self::$fields['Gender']['values']
        ));


        // save results
        return $this->finishValidation();
    }

    public static function getGroupConditions($handle, $matchedCondition)
    {
        $group = Group::getByHandle($handle);

        if (!$group) {
            return false;
        }

        $containedGroups = DB::allRecords('SELECT ID FROM %s WHERE `Left` BETWEEN %u AND %u', array(
            Group::$tableName
            ,$group->Left
            ,$group->Right
        ));

        $containedGroups = array_map(function($group) {
            return $group['ID'];
        },$containedGroups);

        $condition = $matchedCondition['join']['aliasName'].'.GroupID'.' IN ('.implode(',',$containedGroups).')';

        return $condition;
    }

    public function getGroupIDs()
    {
        return array_map(function($Group) {
            return $Group->ID;
        }, $this->Groups);
    }
}
