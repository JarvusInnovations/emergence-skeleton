<?php

class User extends Person implements Emergence\People\IUser
{
    public static $defaultClass = __CLASS__;
    public static $subClasses = array(__CLASS__);
    public static $singularNoun = 'user';
    public static $pluralNoun = 'users';

    // ActiveRecord configuration
    public static $fields = array(
        'Username' => array(
            'unique' => true
        )
        ,'Password' => array(
            'type' => 'string'
            ,'excludeFromData' => true
        )
        ,'AccountLevel' => array(
            'type' => 'enum'
            ,'values' => array('Disabled','Contact','User','Staff','Administrator','Developer')
            ,'default' => 'User'
        )
    );

    // Person
    public static $requireEmail = true;

    // User
    public static $minPasswordLength = 5;
    public static $passwordHasher = 'SHA1';


    public static function __classLoaded()
    {
        // merge User classes into valid Person classes, but not again when child classes are loaded
        if (get_called_class() == __CLASS__) {
            Person::$subClasses = static::$subClasses = array_merge(Person::$subClasses, static::$subClasses);
        }

        // finish ActiveRecord initialization
        parent::__classLoaded();
    }

    public function getValue($name)
    {
        switch ($name) {
            case 'AccountLevelNumeric':
            {
                return static::_getAccountLevelIndex($this->AccountLevel);
            }

            case 'Handle':
            {
                return $this->Username;
            }

            case 'SecretHashKey':
            {
                return SHA1($this->ID.$this->Username.$this->_record['Password']);
            }

            default: return parent::getValue($name);
        }
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        $this->_validator->validate(array(
            'field' => 'Username'
            ,'required' => true
            ,'minlength' => 2
            ,'maxlength' => 30
            ,'errorMessage' => 'Username must be at least two characters long.'
        ));

        $this->_validator->validate(array(
            'field' => 'Username'
            ,'required' => true
            ,'validator' => 'handle'
            ,'errorMessage' => 'Username can only contain letters, numbers, hyphens, and underscores.'
        ));

        // check handle uniqueness
        if ($this->isDirty && !$this->_validator->hasErrors('Username') && $this->Username) {
            $ExistingUser = User::getByUsername($this->Username);

            if ($ExistingUser && ($ExistingUser->ID != $this->ID)) {
                $this->_validator->addError('Username', 'Username already registered.');
            }
        }

        $this->_validator->validate(array(
            'field' => 'AccountLevel'
            ,'validator' => 'selection'
            ,'choices' => self::$fields['AccountLevel']['values']
            ,'required' => false
        ));

        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        if (!$this->Username) {
            $this->Username = static::getUniqueUsername($this->FirstName, $this->LastName);
        }

        return parent::save($deep);
    }

    public function getTitle()
    {
        return sprintf('%s (%s)', $this->Username, $this->AccountLevel);
    }

    public function getHandle()
    {
        return $this->Username;
    }

    public static function getByHandle($handle)
    {
        return static::getByUsername($handle);
    }

    // enable login by email
    public static function getByLogin($username, $password)
    {
        $User = static::getByUsername($username);

        if ($User && is_a($User, __CLASS__) && $User->hasAccountLevel('User') && $User->verifyPassword($password)) {
            return $User;
        } else {
            return null;
        }
    }

    public static function getByUsername($username)
    {
        if (static::fieldExists('Email')) {
            return static::getByWhere(array(
                sprintf('`%2$s` = "%1$s" OR `%3$s` = "%1$s"', DB::escape($username), static::_cn('Username'), static::_cn('Email'))
            ));
        } else {
            return static::getByField('Username', $username);
        }
    }

    public function verifyPassword($password)
    {
        return $this->Password && call_user_func(static::$passwordHasher, $password) == $this->Password;
    }

    public function setClearPassword($password)
    {
        $this->Password = call_user_func(static::$passwordHasher, $password);
    }

    public function hasAccountLevel($accountLevel)
    {
        $accountLevelIndex = static::_getAccountLevelIndex($accountLevel);

        if ($accountLevelIndex === false) {
            return false;
        } else {
            return ($this->AccountLevelNumeric >= $accountLevelIndex);
        }
    }

    public function getPasswordHash()
    {
        return $this->_record[static::_cn('Password')];
    }

    public static function getUniqueUsername($firstName, $lastName, $options = array())
    {
        // apply default options
        $options = array_merge(array(
            'format' => 'short' // full or short
        ), $options);

        // create username
        switch ($options['format']) {
            case 'short':
                $username = $firstName[0].$lastName;
                break;
            case 'full':
                $username = $firstName.'_'.$lastName;
                break;
            default:
                throw new Exception('Unknown username format.');
        }

        // strip bad characters
        $username = $strippedText = preg_replace(
            array('/\s+/', '/[^a-zA-Z0-9\-_]+/')
            , array('_', '-')
            , strtolower($username)
        );

        $incarnation = 1;
        while (static::getByWhere(array('Username'=>$username))) {
            // TODO: check for repeat posting here?
            $incarnation++;

            $username = $strippedText.$incarnation;
        }

        return $username;
    }





    protected static function _getAccountLevelIndex($accountLevel)
    {
        return array_search($accountLevel, self::$fields['AccountLevel']['values']);
    }
}