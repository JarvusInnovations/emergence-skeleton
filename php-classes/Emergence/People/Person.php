<?php

namespace Emergence\People;

use DB;
use Emergence\Comments\Comment;
use PhotoMedia;
use VersionedRecord;

class Person extends VersionedRecord implements IPerson
{
    public static $classLabel = 'Person / Contact';

    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = [__CLASS__];

    // VersionedRecord configuration
    public static $historyTable = 'history_people';

    // ActiveRecord configuration
    public static $tableName = 'people';
    public static $singularNoun = 'person';
    public static $pluralNoun = 'people';
    public static $collectionRoute = '/people';

    public static $fields = [
        'FirstName' => [
            'includeInSummary' => true
        ],
        'LastName' => [
            'includeInSummary' => true
        ],
        'MiddleName' => [
            'notnull' => false
        ],
        'PreferredName' => [
            'default' => null
        ],
        'Gender' => [
            'type' => 'enum',
            'values' => ['Male', 'Female'],
            'notnull' => false
        ],
        'BirthDate' => [
            'type' => 'date',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'Email' => [
            'notnull' => false,
            'unique' => true,
            'accountLevelEnumerate' => 'User'
        ],
        'Phone' => [
            'type' => 'decimal',
            'length' => '15,0',
            'unsigned' => true,
            'notnull' => false,
            'accountLevelEnumerate' => 'User'
        ],
        'Location' => [
            'notnull' => false
        ],
        'About' => [
            'type' => 'clob',
            'notnull' => false
        ],
        'PrimaryPhotoID' => [
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => false
        ]
    ];

    public static $relationships = [
        'GroupMemberships' => [
            'type' => 'one-many',
            'class' => Groups\GroupMember::class,
            'indexField' => 'GroupID',
            'foreign' => 'PersonID'
        ],
        'Groups' => [
            'type' => 'many-many',
            'class' => Groups\Group::class,
            'linkClass' => Groups\GroupMember::class,
            'linkLocal' => 'PersonID',
            'linkForeign' => 'GroupID'
        ],
        'PrimaryPhoto' => [
            'type' => 'one-one',
            'class' => PhotoMedia::class,
            'local' => 'PrimaryPhotoID'
        ],
        'Photos' => [
            'type' => 'context-children',
            'class' => PhotoMedia::class,
            'contextClass' => __CLASS__
        ],
        'Comments' => [
            'type' => 'context-children',
            'class' => Comment::class,
            'contextClass' => __CLASS__,
            'order' => ['ID' => 'DESC']
        ]
    ];

    public static $dynamicFields = [
        'PrimaryPhoto',
        'Photos',
        'groupIDs' => [
            'method' => 'getGroupIDs'
        ]
    ];

    public static $searchConditions = [
        'FirstName' => [
            'qualifiers' => ['any', 'name', 'fname', 'firstname', 'first'],
            'points' => 2,
            'sql' => 'FirstName LIKE "%%%s%%"'
        ],
        'LastName' => [
            'qualifiers' => ['any', 'name', 'lname', 'lastname', 'last'],
            'points' => 2,
            'sql' => 'LastName LIKE "%%%s%%"'
        ],
        'Username' => [
            'qualifiers' => ['any', 'username', 'uname', 'user'],
            'points' => 2,
            'sql' => 'Username LIKE "%%%s%%"'
        ],
        'Gender' => [
            'qualifiers' => ['gender', 'sex'],
            'points' => 2,
            'sql' => 'Gender LIKE "%s"'
        ],
        'Class' => [
            'qualifiers' => ['class'],
            'points' => 2,
            'sql' => 'Class LIKE "%%%s%%"'
        ],
        'AccountLevel' => [
            'qualifiers' => ['accountlevel'],
            'points' => 2,
            'sql' => 'AccountLevel LIKE "%%%s%%"'
        ],
        'Group' => [
            'qualifiers' => ['group'],
            'points' => 1,
            'join' => [
                'className' => Groups\GroupMember::class,
                'aliasName' => 'GroupMember',
                'localField' => 'ID',
                'foreignField' => 'PersonID'
            ],
            'callback' => 'getGroupConditions'
        ]
    ];

    public static $validators = [
        'Class' => [
            'validator' => 'selection',
            'choices' => [], // filled dynamically in __classLoaded
            'required' => false
        ],
        'FirstName' => [
            'minlength' => 2,
            'required' => true,
            'errorMessage' => 'First name is required.'
        ],
        'LastName' => [
            'minlength' => 2,
            'required' => true,
            'errorMessage' => 'Last name is required.'
        ],
        'Email' => [
            'field' => 'Email',
            'validator' => 'email',
            'required' => false
        ],
        'Phone' => [
            'validator' => 'phone',
            'required' => false
        ],
        'BirthDate' => [
            'validator' => 'date_ymd',
            'required' => false
        ],
        'Gender' => [
            'validator' => 'selection',
            'required' => false,
            'choices' => [] // filled dynamically in __classLoaded
        ]
    ];

    // Person
    public static function __classLoaded()
    {
        if (get_called_class() == __CLASS__) {
            self::$validators['Gender']['choices'] = self::$fields['Gender']['values'];
        }

        self::$validators['Class']['choices'] = static::getSubClasses();

        parent::__classLoaded();
    }

    public function getValue($name)
    {
        switch ($name) {
            case 'FullName':
                return $this->getFullName();
            case 'FirstInitial':
                return strtoupper(substr($this->FirstName, 0, 1));
            case 'LastInitial':
                return strtoupper(substr($this->LastName, 0, 1));
            case 'FirstNamePossessive':
                if (substr($this->FirstName, -1) == 's') {
                    return $this->FirstName . '\'';
                } else {
                    return $this->FirstName . '\'s';
                }
                // no break
            case 'FullNamePossessive':
                $fullName = $this->FullName;

                if (substr($fullName, -1) == 's') {
                    return $fullName . '\'';
                } else {
                    return $fullName . '\'s';
                }
                // no break
            case 'EmailRecipient':
                return sprintf('"%s" <%s>', $this->FullName, $this->Email);
            default:
                return parent::getValue($name);
        }
    }

    public function getTitle()
    {
        return $this->getFullName();
    }

    public function getFullName()
    {
        return $this->FirstName . ' ' . $this->LastName;
    }

    public static function getByEmail($email)
    {
        return static::getByField('Email', $email);
    }

    public static function getByFullName($firstName, $lastName)
    {
        return static::getByWhere([
            'FirstName' => $firstName,
            'LastName' => $lastName
        ]);
    }

    public static function getOrCreateByFullName($firstName, $lastName, $save = false)
    {
        if ($Person = static::getByFullName($firstName, $lastName)) {
            return $Person;
        } else {
            return static::create([
                'FirstName' => $firstName,
                'LastName' => $lastName
            ], $save);
        }
    }

    public static function parseFullName($fullName)
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        if (count($parts) != 2) {
            throw new \Exception('Full name must contain a first and last name separated by a space.');
        }

        return [
            'FirstName' => $parts[0],
            'LastName' => $parts[1]
        ];
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        // strip any non-digit characters from phone before validation
        if ($this->Phone) {
            $this->Phone = preg_replace('/\D/', '', $this->Phone);
        }

        // check email uniqueness
        if ($this->isDirty && !$this->_validator->hasErrors('Email') && $this->Email) {
            $ExistingPerson = static::getByField('Email', $this->Email);

            if ($ExistingPerson && ($ExistingPerson->ID != $this->ID)) {
                $this->_validator->addError('Email', 'Email already registered to another account.');
            }
        }

        // save results
        return $this->finishValidation();
    }

    public static function getGroupConditions($handle, $matchedCondition)
    {
        $group = Groups\Group::getByHandle($handle);

        if (!$group) {
            return false;
        }

        $containedGroups = DB::allRecords('SELECT ID FROM %s WHERE `Left` BETWEEN %u AND %u', [
            Groups\Group::$tableName,
            $group->Left,
            $group->Right
        ]);

        $containedGroups = array_map(function ($group) {
            return $group['ID'];
        }, $containedGroups);

        $condition = $matchedCondition['join']['aliasName'].'.GroupID'.' IN ('.implode(',', $containedGroups).')';

        return $condition;
    }

    public function getGroupIDs()
    {
        return array_map(function ($Group) {
            return $Group->ID;
        }, $this->Groups);
    }
}
