<?php

use Emergence\People\Groups\Group;

class PeopleRequestHandler extends RecordsRequestHandler
{
    // RecordRequestHandler configuration
    public static $recordClass = 'Person';

    public static function handleRecordsRequest($action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case '*classes':
                return static::handleClassesRequest();
            case '*account-levels':
                return static::handleAccountLevelsRequest();
            default:
                return parent::handleRecordsRequest($action);
        }
    }

    public static function handleClassesRequest()
    {
        return static::respond('classes', array(
            'data' => Person::getSubClasses()
            ,'default' => Person::getDefaultClass()
        ));
    }

    public static function handleAccountLevelsRequest()
    {
        return static::respond('account-levels', array(
            'data' => User::getFieldOptions('AccountLevel', 'values')
            ,'default' => User::getFieldOptions('AccountLevel', 'default')
        ));
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

    public static function handleRecordRequest(ActiveRecord $Person, $action = false)
    {
        switch ($action ?: $action = static::shiftPath()) {
            case 'temporary-password':
            case '*temporary-password':
                return static::handleTemporaryPasswordRequest($Person);
            case 'thumbnail':
                return static::handleThumbnailRequest($Person);
            default:
                return parent::handleRecordRequest($Person, $action);
        }
    }

    public static function handleTemporaryPasswordRequest(Person $Person)
    {
        $GLOBALS['Session']->requireAccountLevel('Administrator');

        if (!$Person instanceof User) {
            return static::throwInvalidRequestError('only a user can have a temporary password');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $Person->setTemporaryPassword();
            $Person->save();
        }

        return static::respond('temporaryPassword', array(
            'success' => true,
            'temporaryPassword' => $Person->TemporaryPassword
        ));
    }

    public static function handleThumbnailRequest(Person $Person)
    {
        return MediaRequestHandler::handleThumbnailRequest($Person->PrimaryPhoto ? $Person->PrimaryPhoto : Media::getBlank('person'));
    }
}