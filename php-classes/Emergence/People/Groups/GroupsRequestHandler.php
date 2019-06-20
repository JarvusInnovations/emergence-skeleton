<?php

namespace Emergence\People\Groups;

use ActiveRecord;
use DB;
use RecordsRequestHandler;

class GroupsRequestHandler extends RecordsRequestHandler
{
    public static $recordClass = 'Emergence\People\Groups\Group';
    public static $accountLevelRead = 'User';
    public static $browseOrder = ['Left' => 'ASC'];

    public static function handleBrowseRequest($options = [], $conditions = [], $responseID = null, $responseData = [])
    {
        if (!empty($_REQUEST['parentGroup']) && $_REQUEST['parentGroup'] != 'any') {
            $conditions['ParentID'] = $_REQUEST['parentGroup'];
        } elseif ($_REQUEST['parentGroup'] != 'any') {
            $conditions['ParentID'] = null;
        }

        if ($_REQUEST['query']) {
            $conditions[] = sprintf('Name LIKE "%%%s%%"', DB::escape($_REQUEST['query']));
        }

        if (!empty($_REQUEST['q'])) {
            $conditions[] = 'Name LIKE "%'.DB::escape($_REQUEST['q']).'%"';
        }

        return parent::handleBrowseRequest($options, $conditions);
    }

    public static function handleRecordRequest(ActiveRecord $Group, $action = false)
    {
        switch ($action ? $action : static::shiftPath()) {
            case 'members':
                return static::handleMembersRequest($Group);
            default:
                return parent::handleRecordRequest(Group, $action);
        }
    }

    public static function handleMembersRequest(Group $Group)
    {
        return static::respond('members', [
            'success' => true
            ,'data' => $Group->getAllPeople()
            ,'group' => $Group
        ]);
    }
}
