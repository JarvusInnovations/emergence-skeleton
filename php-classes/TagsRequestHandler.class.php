<?php

class TagsRequestHandler extends RecordsRequestHandler
{
    // RecordRequestHandler configuration
    public static $recordClass = 'Tag';
    public static $accountLevelBrowse = false;
    public static $accountLevelAssign = 'User';

    public static function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'assign':
            {
                return static::handleMultiAssignRequest();
            }

            default:
            {
                return parent::handleRecordsRequest($action);
            }
        }
    }

    public static function handleRecordRequest(ActiveRecord $Tag, $action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'items':
            {
                return static::handleTagItemsRequest($Tag);
            }

            default:
            {
                return parent::handleRecordRequest($Tag, $action);
            }
        }
    }

    public static function handleTagItemsRequest(Tag $Tag)
    {
        $conditions = array(
            'TagID' => $Tag->ID
        );

        if (!empty($_REQUEST['Class']) && Validators::className($_REQUEST['Class'])) {
            $conditions['ContextClass'] = $_REQUEST['Class'];
        }

        return static::respond('tagItems', array(
            'success' => true
            ,'data' => TagItem::getAllByWhere($conditions)
        ));
    }
    
    public static function handleMultiAssignRequest()
    {
        if (static::$accountLevelAssign) {
            $GLOBALS['Session']->requireAccountLevel(static::$accountLevelAssign);
        }

        if (static::$responseMode == 'json' && in_array($_SERVER['REQUEST_METHOD'], array('POST','PUT'))) {
            $_REQUEST = JSON::getRequestData();
        }

        if (empty($_REQUEST['data']) || !is_array($_REQUEST['data'])) {
            return static::throwInvalidRequestError('Assign expects "data" field as array of assignments');
        }

        $className = static::$recordClass;
        $results = array();

        foreach ($_REQUEST['data'] AS $datum) {
            if (!$Tag = $className::getByID($datum['TagID'])) {
                return static::throwNotFoundError();
            }

            $TagItem = $Tag->assignItem($datum['ContextClass'], $datum['ContextID']);

            if ($TagItem->isNew) {
                $results[] = $TagItem;
            }
        }

        return static::respond($className::$pluralNoun.'Saved', array(
            'success' => true
            ,'data' => $results
        ));
    }
}
