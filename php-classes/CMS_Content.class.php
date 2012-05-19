<?php

abstract class CMS_Content extends VersionedRecord
{
    // VersionedRecord configuration
    static public $historyTable = 'history_content';

    // ActiveRecord configuration
    static public $tableName = 'content';
    static public $singularNoun = 'content';
    static public $pluralNoun = 'contents';
    
    // required for shared-table subclassing support
    static public $rootClass = __CLASS__;
    static public $defaultClass = __CLASS__;
    static public $subClasses = array('CMS_Page','CMS_BlogPost');

    static public $fields = array(
        //'ContextClass' => null // uncomment to enable
        //,'ContextID' => null
        
        'Title'
        ,'Handle' => array(
            'unique' => true
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
        ,'Published' => array(
            'type'  =>  'timestamp'
            ,'notnull' => false
        )
    );
    
    
    static public $relationships = array(
		'GlobalHandle' => array(
			'type' => 'handle'
		)
		,'Author' =>  array(
            'type' =>  'one-one'
            ,'class' => 'Person'
        )
		,'Items' => array(
        	'type' => 'one-many'
        	,'class' => 'CMS_ContentItem'
        	,'foreign' => 'ContentID'
        	,'conditions' => 'Status != "Deleted"'
        	,'order' => array('Order','ID')
        )
        ,'Tags' => array(
        	'type' => 'many-many'
        	,'class' => 'Tag'
        	,'linkClass' => 'TagItem'
        	,'linkLocal' => 'ContextID'
        	,'conditions' => array('Link.ContextClass = "CMS_BlogPost"')
        )
		,'Categories' => array(
        	'type' => 'many-many'
        	,'class' => 'Category'
        	,'linkClass' => 'CategoryItem'
        	,'linkLocal' => 'ContextID'
        	,'conditions' => array('Link.ContextClass = "CMS_BlogPost"')
        )
    );

    
    static public function getAllPublishedByContextObject(ActiveRecord $Context, $options = array())
    {
		$options = Site::prepareOptions($options, array(
			'conditions' => array()
			,'order' => array('Published' => 'DESC')
		));
		
		$options['conditions']['Status'] = 'Published';
		$options['conditions'][] = 'Published IS NULL OR Published <= CURRENT_TIMESTAMP';
		
		if(get_called_class() != __CLASS__)
			$options['conditions']['Class'] = get_called_class();
    
	    return static::getAllByContextObject($Context, $options);
    }
   
    static public function getAllPublishedByAuthor(Person $Author, $options = array())
    {
		$options = Site::prepareOptions($options, array(
			'order' => array('Published' => 'DESC')
		));
		
		$conditions = array(
			'AuthorID' => $Author->ID
			,'Status' => 'Published'
			,'Published IS NULL OR Published <= CURRENT_TIMESTAMP'
		);
				
		if(get_called_class() != __CLASS__)
			$conditions['Class'] = get_called_class();
    
	    return static::getAllByWhere($conditions, $options);
    }
   
    static public function getByHandle($handle)
    {
        return static::getByField('Handle', $handle, true);
    }
    
    public function validate()
    {
        // call parent
        parent::validate();
        
        $this->_validator->validate(array(
            'field' => 'Title'
            ,'errorMessage' => 'A title is required'
        ));
        
        
		// implement handles
		GlobalHandleBehavior::onValidate($this, $this->_validator);				
        
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
        
    	// set published
    	if(!$this->Published && $this->Status == 'Published')
    	{
    		$this->Published = time();
    	}
    	
        // if not handle specified create one
		if(!$this->Handle)
			$this->Handle = strtolower(static::getUniqueHandle($this->Title));
        
		// implement handles
		GlobalHandleBehavior::onSave($this, $this->Title);				
    
        // call parent
        parent::save($deep, $createRevision);
    }
    
    public function destroy() {
    	
    	// delete all TagItems
    	DB::nonQuery("DELETE FROM `" . TagItem::$tableName . "` WHERE `ContextClass`='" . $this->Class . "' AND `ContextID`='" . $this->ID ."'");
    	
    	// delete all CategoryItems
    	DB::nonQuery("DELETE FROM `" . CategoryItem::$tableName . "` WHERE `ContextClass`='" . $this->Class . "' AND `ContextID`='" . $this->ID ."'");
    	
    	return parent::destroy();
    }

	public function getData()
	{
		return array_merge(parent::getData(), array(
			'items' => array_values(JSON::translateObjects($this->Items))
			,'tags' => array_values(JSON::translateObjects($this->Tags))
			,'categories' => array_values(JSON::translateObjects($this->Categories))
			,'Author' => $this->Author ? $this->Author->getData() : null
		));
	
	}
		    
    
    public function renderBody()
    {
    	return join('', array_map(function($Item){
    		return $Item->renderBody();
    	}, $this->Items));
    }

}
