<?php

class CMS_Page extends CMS_Content
{
    // ActiveRecord configuration
    public static $defaultClass = __CLASS__;
    public static $singularNoun = 'page';
    public static $pluralNoun = 'pages';

    public static $fields = array(
        'LayoutClass' => array(
            'type' => 'enum'
            ,'values' => array('OneColumn')
            ,'default' => 'OneColumn'
        )
        ,'LayoutConfig'  => 'serialized'
    );


    public static function getAllPublishedByContextObject(ActiveRecord $Context, $options = array())
    {
        $options = MICS::prepareOptions($options, array(
            'conditions' => array()
        ));

        $options['conditions']['Class'] = __CLASS__;

        return parent::getAllPublishedByContextObject($Context, $options);
    }
}

