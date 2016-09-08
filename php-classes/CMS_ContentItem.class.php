<?php

abstract class CMS_ContentItem extends VersionedRecord
{
    // VersionedRecord configuration
    public static $historyTable = 'history_content_item';

    // ActiveRecord configuration
    public static $tableName = 'content_item';
    public static $singularNoun = 'content item';
    public static $pluralNoun = 'content items';

    // required for shared-table subclassing support
    public static $rootClass = __CLASS__;
    public static $defaultClass = 'CMS_TextContent';
    public static $subClasses = array('CMS_TextContent','CMS_RichTextContent','CMS_MediaContent','CMS_AlbumContent');

    public static $fields = array(
        'Title' => array(
            'notnull' => false
            ,'blankisnull' => true
        )
        ,'ContentID' => array(
            'type'  => 'integer'
            ,'unsigned' => true
            ,'index' => true
        )
        ,'AuthorID' => array(
            'type'  =>  'integer'
            ,'unsigned' => true
        )
        ,'Status' => array(
            'type' => 'enum'
            ,'values' => array('Draft','Published','Hidden','Deleted')
            ,'default' => 'Published'
        )
        ,'Order' => array(
            'type' => 'integer'
            ,'unsigned' => true
            ,'notnull' => false
        )
        ,'Data' => 'serialized'
    );


    public static $relationships = array(
        'Author'    =>  array(
            'type'  =>  'one-one'
            ,'class' => 'Person'
        )
        ,'Content' =>   array(
            'type'  =>  'one-one'
            ,'class' => 'CMS_Content'
        )
    );

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        // set author
        if (!$this->AuthorID) {
            $this->Author = $_SESSION['User'];
        }

        // call parent
        parent::save($deep);
    }



    abstract public function renderBody();
}