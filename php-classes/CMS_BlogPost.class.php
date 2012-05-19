<?php
class CMS_BlogPost extends CMS_Content
{
    // ActiveRecord configuration
    static public $defaultClass = __CLASS__;
    static public $singularNoun = 'blog post';
    static public $pluralNoun = 'blog posts';
    
    
    static public function getRecentlyPublished($limit = 5)
    {
    	return static::getAllByWhere(array(
			'Class'=>'CMS_BlogPost'
			,'Status' => 'Published'
			,'Published IS NULL OR Published <= CURRENT_TIMESTAMP'
		),array(
			'order' => array('Published' => 'DESC')
			,'limit' => $limit
		));
    }
}

