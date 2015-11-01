<?php
class CMS_BlogPost extends CMS_Content
{
    // ActiveRecord configuration
    public static $defaultClass = __CLASS__;
    public static $singularNoun = 'blog post';
    public static $pluralNoun = 'blog posts';


    public static function getRecentlyPublished($limit = 5)
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

