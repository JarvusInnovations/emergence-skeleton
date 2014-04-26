<?php

class GroupsRequestHandler extends RecordsRequestHandler
{
    static public $recordClass = 'Group';
    static public $accountLevelRead = 'User';


    static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
	{
        if(!empty($_REQUEST['parentGroup']) && $_REQUEST['parentGroup'] != 'any')
        {
            $conditions['ParentID'] = $_REQUEST['parentGroup'];
        }
        else if($_REQUEST['parentGroup'] != 'any')
        {
            $conditions['ParentID'] = NULL;
        }
        
        if($_REQUEST['query'])
    	{
    		$conditions[] = sprintf('Name LIKE "%%%s%%"', DB::escape($_REQUEST['query']));
    	}
        
        if(!empty($_REQUEST['q'])) {
        	$conditions[] = 'Name LIKE "%' . DB::escape($_REQUEST['q']) . '%"';
        }
        
        return parent::handleBrowseRequest($options, $conditions);
	}

    static public function handleRecordRequest(ActiveRecord $Group, $action = false)
	{
		switch($action ? $action : static::shiftPath())
		{
			case 'members':
				return static::handleMembersRequest($Group);
			
			default:
				return parent::handleRecordRequest(Group, $action);
		}
	}
    
    
    static public function handleMembersRequest(Group $Group)
    {
        return static::respond('members', array(
            'success' => true
            ,'data' => $Group->getAllPeople()
            ,'group' => $Group
        ));
    }
}