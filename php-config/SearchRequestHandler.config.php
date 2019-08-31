<?php

if (!empty($GLOBALS['Session']) && $GLOBALS['Session']->hasAccountLevel('User')) {
    SearchRequestHandler::$searchClasses['User'] = array(
        'weight' => -1000,
        'fields' => array(
            array(
                'field' => 'FirstName'
                ,'method' => 'like'
            ), array(
                'field' => 'LastName'
                ,'method' => 'like'
                ), array(
                'field' => 'Username'
                ,'method' => 'like'
            ), array(
                'field' => 'FullName'
                ,'method' => 'sql'
                ,'sql' => 'CONCAT(FirstName," ",LastName) = "%s"'
            )
        )
        ,'conditions' => array('AccountLevel != "Disabled"')
    );
}

SearchRequestHandler::$searchClasses['Tag'] = array(
    'weight' => 1000,
    'fields' => array(
        'Title'
        ,array(
            'field' => 'Handle'
            ,'method' => 'like'
        )
    )
);

SearchRequestHandler::$searchClasses['Emergence\CMS\Page'] = array(
    'weight' => -5000,
    'fields' => array(
        'Title'
        ,array(
            'field' => 'Handle'
            ,'method' => 'like'
        )
    )
    ,'conditions' => array('Class' => 'Emergence\CMS\Page', 'Status' => 'Published', 'Published IS NULL OR Published <= CURRENT_TIMESTAMP')
);

SearchRequestHandler::$searchClasses['Emergence\CMS\BlogPost'] = array(
    'weight' => -100,
    'fields' => array(
        'Title'
        ,array(
            'field' => 'Handle'
            ,'method' => 'like'
        )
    )
    ,'conditions' => array('Class' => 'Emergence\CMS\BlogPost', 'Status' => 'Published', 'Published IS NULL OR Published <= CURRENT_TIMESTAMP')
);
