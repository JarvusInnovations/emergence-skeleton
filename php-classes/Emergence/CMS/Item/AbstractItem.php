<?php

namespace Emergence\CMS\Item;

abstract class AbstractItem extends \VersionedRecord
{
    // ActiveRecord configuration
    public static $tableName = 'content_items';
    public static $singularNoun = 'content item';
    public static $pluralNoun = 'content items';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
    public static $defaultClass = 'Emergence\CMS\Item\Text';
    public static $subClasses = [
        'Emergence\CMS\Item\Album'
        ,'Emergence\CMS\Item\Embed'
        ,'Emergence\CMS\Item\Media'
        ,'Emergence\CMS\Item\RichText'
        ,'Emergence\CMS\Item\Text'
        ,'Emergence\CMS\Item\Markdown'
    ];

    public static $fields = [
        'Title' => [
            'notnull' => false
            ,'blankisnull' => true
        ]
        ,'ContentID' => [
            'type'  => 'integer'
            ,'unsigned' => true
            ,'index' => true
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
        ,'Order' => [
            'type' => 'integer'
            ,'unsigned' => true
            ,'notnull' => false
        ]
        ,'Data' => 'json'
    ];

    public static $relationships = [
        'Author'    =>  [
            'type'  =>  'one-one'
            ,'class' => 'Person'
        ]
        ,'Content' =>   [
            'type'  =>  'one-one'
            ,'class' => 'Emergence\CMS\AbstractContent'
        ]
    ];

    public static $validators = [
        'Content' => 'require-relationship'
    ];

    public function validate($deep = true)
    {
        // call parent
        parent::validate();

        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        // set author
        if (!$this->AuthorID && !empty($_SESSION) && !empty($_SESSION['User'])) {
            $this->Author = $_SESSION['User'];
        }

        // call parent
        parent::save($deep);
    }


    abstract public function renderBody();
}
