<?php

abstract class CMS_ContentItem extends VersionedRecord
{
    // VersionedRecord configuration
    static public $historyTable = 'history_content_item';

    // ActiveRecord configuration
    static public $tableName = 'content_item';
    static public $singularNoun = 'content item';
    static public $pluralNoun = 'content items';
    
    // required for shared-table subclassing support
    static public $rootClass = __CLASS__;
    static public $defaultClass = 'CMS_TextContent';
    static public $subClasses = array('CMS_TextContent','CMS_RichTextContent','CMS_MediaContent','CMS_AlbumContent');

    static public $fields = array(
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
    
    
    static public $relationships = array(
        'Author'    =>  array(
            'type'  =>  'one-one'
            ,'class' => 'Person'
        )
        ,'Content' =>   array(
            'type'  =>  'one-one'
            ,'class' => 'CMS_Content'
        )
    );
    
    public function validate()
    {
        // call parent
        parent::validate();
        
        // save results
        return $this->finishValidation();
    }
    
    public function save($deep = true, $createRevision = true)
    {
    	// set author
    	if(!$this->AuthorID)
    	{
    		$this->Author = $_SESSION['User'];
    	}
        
        // call parent
        parent::save($deep, $createRevision);
    }
    
    
        
    abstract public function renderBody();

}