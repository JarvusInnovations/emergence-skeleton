<?php

class PeopleRequestHandler extends RecordsRequestHandler
{
    // RecordRequestHandler configuration
    static public $recordClass = 'Person';

    static public function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case '*classes':
                return static::respond('classes', array(
                    'data' => Person::getStaticSubClasses()
                    ,'default' => Person::getStaticDefaultClass()
                ));
            case '*account-levels':
                return static::respond('account-levels', array(
                    'data' => User::getFieldOptions('AccountLevel', 'values')
                    ,'default' => User::getFieldOptions('AccountLevel', 'default')
                ));
            default:
                return parent::handleRecordsRequest($action);
        }
    }

    static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
    {
        if ($_REQUEST['q'] != 'all') {
            $conditions[] = 'AccountLevel != "Disabled"';
        }

        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }

    static protected function onRecordSaved(ActiveRecord $Person, $requestData)
    {
        if (isset($requestData['groupIDs'])) {
            Group::setPersonGroups($Person, $requestData['groupIDs']);
        }
    }
}