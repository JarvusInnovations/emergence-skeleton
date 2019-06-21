<?php

namespace Emergence\CMS;

use ActiveRecord;
use Emergence\Comments\Comment;
use Emergence\People\IPerson;
use Emergence\People\Person;
use HandleBehavior;
use JSON;
use Tag;
use TagItem;

abstract class AbstractContent extends \VersionedRecord
{
    // ActiveRecord configuration
    public static $tableName = 'content';
    public static $singularNoun = 'content';
    public static $pluralNoun = 'contents';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = [
        Page::class,
        BlogPost::class
    ];

    public static $searchConditions = [
        'Title' => [
            'qualifiers' => ['any', 'title']
            ,'points' => 2
            ,'sql' => 'Title Like "%%%s%%"'
        ]
        ,'Handle' => [
            'qualifiers' => ['any', 'handle']
            ,'points' => 2
            ,'sql' => 'Handle Like "%%%s%%"'
        ]
    ];

    public static $fields = [
        'ContextClass' => [
            'type' => 'string'
            ,'notnull' => false
        ]
        ,'ContextID' => [
            'type' => 'uint'
            ,'notnull' => false
        ]
        ,'Title'
        ,'Handle' => [
            'unique' => true
        ]
        ,'AuthorID' => [
            'type'  =>  'integer'
            ,'unsigned' => true
        ]
        ,'Status' => [
            'type' => 'enum'
            ,'values' => ['Draft','Published','Hidden','Deleted']
            ,'default' => 'Published'
        ]
        ,'Published' => [
            'type'  =>  'timestamp'
            ,'notnull' => false
            ,'index' => true
        ]
        ,'Visibility' => [
            'type' => 'enum'
            ,'values' => ['Public','Private']
            ,'default' => 'Public'
        ]
        ,'Summary' => [
            'type' => 'clob'
            ,'notnull' => false
        ]
    ];


    public static $relationships = [
        'Context' => [
            'type' => 'context-parent'
        ]
        ,'Author' =>  [
            'type' =>  'one-one'
            ,'class' => Person::class
        ]
        ,'Items' => [
            'type' => 'one-many'
            ,'class' => Item\AbstractItem::class
            ,'foreign' => 'ContentID'
            ,'conditions' => 'Status != "Deleted"'
            ,'order' => ['Order','ID']
        ]
        ,'Tags' => [
            'type' => 'many-many'
            ,'class' => Tag::class
            ,'linkClass' => TagItem::class
            ,'linkLocal' => 'ContextID'
            ,'conditions' => ['Link.ContextClass = "Emergence\\\\CMS\\\\AbstractContent"']
        ]
        ,'Comments' => [
            'type' => 'context-children'
            ,'class' => Comment::class
            ,'order' => ['ID' => 'DESC']
        ]
    ];

    public static $dynamicFields = [
        'tags' => 'Tags'
        ,'items' => 'Items'
        ,'Author'
        ,'Context'
    ];


    public function userCanReadRecord(IPerson $User = null)
    {
        $User = $User ?: $this->getUserFromEnvironment();

        // author and staff can always read
        if ($User && ($User->ID == $this->AuthorID || $User->hasAccountLevel('Staff'))) {
            return true;
        }

        // only above exempted users can view non-published content
        if ($this->Status != 'Published') {
            return false;
        }

        // only logged-in users can view non-public content
        if ($this->Visibility != 'Public' && (!$User || !$User->hasAccountLevel('User'))) {
            return false;
        }

        return true;
    }

    public static function getAllPublishedByContextObject(ActiveRecord $Context, $options = [])
    {
        $options = array_merge([
            'conditions' => [],
            'order' => ['Published' => 'DESC']
        ], $options);

        if (empty($GLOBALS['Session']) || !$GLOBALS['Session']->Person) {
            $options['conditions']['Visibility'] = 'Public';
        }

        $options['conditions']['Status'] = 'Published';
        $options['conditions'][] = 'Published IS NULL OR Published <= CURRENT_TIMESTAMP';

        if (get_called_class() != __CLASS__) {
            $options['conditions']['Class'] = get_called_class();
        }

        return static::getAllByContextObject($Context, $options);
    }

    public static function getAllPublishedByAuthor(IPerson $Author, $options = [])
    {
        $options = array_merge([
            'conditions' => [],
            'order' => ['Published' => 'DESC']
        ], $options);

        if (empty($GLOBALS['Session']) || !$GLOBALS['Session']->Person) {
            $options['conditions']['Visibility'] = 'Public';
        }

        $options['conditions']['AuthorID'] = $Author->ID;
        $options['conditions']['Status'] = 'Published';
        $options['conditions'][] = 'Published IS NULL OR Published <= CURRENT_TIMESTAMP';

        if (get_called_class() != __CLASS__) {
            $options['conditions']['Class'] = get_called_class();
        }

        return static::getAllByWhere($options['conditions'], $options);
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate();

        $this->_validator->validate([
            'field' => 'Title'
            ,'errorMessage' => 'A title is required'
        ]);

        // implement handles
        HandleBehavior::onValidate($this, $this->_validator);

        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        // set author
        if (!$this->AuthorID && !empty($_SESSION) && !empty($_SESSION['User'])) {
            $this->Author = $_SESSION['User'];
        }

        // set published
        if (!$this->Published && $this->Status == 'Published') {
            $this->Published = time();
        }

        // implement handles
        HandleBehavior::onSave($this);

        // call parent
        parent::save($deep);
    }

    public function renderBody()
    {
        return join('', array_map(function ($Item) {
            return $Item->renderBody();
        }, $this->Items));
    }
}
