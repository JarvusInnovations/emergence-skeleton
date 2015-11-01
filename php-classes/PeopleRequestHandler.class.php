<?php

class PeopleRequestHandler extends RecordsRequestHandler
{
    // RecordRequestHandler configuration
    public static $recordClass = 'Person';

    public static function handleRecordsRequest($action = false)
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

    public static function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
    {
        if ($_REQUEST['q'] != 'all') {
            $conditions[] = 'AccountLevel != "Disabled"';
        }

        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }

    protected static function onRecordSaved(ActiveRecord $Person, $requestData)
    {
        if (isset($requestData['groupIDs'])) {
            Group::setPersonGroups($Person, $requestData['groupIDs']);
        }
    }

    public static function getRecordByHandle($handle)
    {
        if (ctype_digit($handle) || is_int($handle)) {
            return Person::getByID($handle);
        } else {
            return User::getByUsername($handle);
        }
    }
}