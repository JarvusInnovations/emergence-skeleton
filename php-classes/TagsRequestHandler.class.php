<?php

class TagsRequestHandler extends RecordsRequestHandler
{
    // RecordRequestHandler configuration
	static public $recordClass = 'Tag';
	static public $accountLevelBrowse = false;
	static public $accountLevelAssign = 'User';


	static public function handleRecordsRequest($action = false)
	{
		switch($action ? $action : $action = static::shiftPath())
		{
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
	
	
	static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = NULL, $responseData = array())
	{
		$conditions = array();
		
		if(!empty($_REQUEST['q']))
		{
			if($_REQUEST['valuesqry'] == 'true')
			{
				$handles = explode('|', $_REQUEST['q']);
				$conditions = 'Handle IN ("'.join('","',DB::escape($handles)).'")';
			}
			elseif(ctype_digit($_REQUEST['q']))
			{
				$conditions[] = 'ID = '.$_REQUEST['q'];
			}
			else
			{
				$conditions[] = sprintf('Title LIKE "%%%1$s%%" OR Handle LIKE "%%%1$s%%"', DB::escape($_REQUEST['q']));
			}
		}
	
		return static::respond('tags', array(
			'success' => true
			,'data' => Tag::getAllByWhere($conditions)
		));
	}

	
	static public function handleRecordRequest(ActiveRecord $Tag, $action = false)
	{
		switch($action ? $action : $action = static::shiftPath())
		{
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
	
	static public function handleTagItemsRequest(Tag $Tag)
	{
		$conditions = array(
			'TagID' => $Tag->ID
		);
		
		if(!empty($_REQUEST['Class']) && Validators::className($_REQUEST['Class']))
		{
			$conditions['ContextClass'] = $_REQUEST['Class'];
		}
		
		
		return static::respond('tagItems', array(
			'success' => true
			,'data' => TagItem::getAllByWhere($conditions)
		));
	
	}

	
	static public function handleMultiAssignRequest()
	{
		if(static::$accountLevelAssign)
		{
			$GLOBALS['Session']->requireAccountLevel(static::$accountLevelAssign);
		}
		
		if(static::$responseMode == 'json' && in_array($_SERVER['REQUEST_METHOD'], array('POST','PUT')))
		{
			$_REQUEST = JSON::getRequestData();
		}
				
		if(empty($_REQUEST['data']) || !is_array($_REQUEST['data']))
		{
			return static::throwInvalidRequestError('Assign expects "data" field as array of assignments');
		}
		
		$className = static::$recordClass;
		$results = array();

		foreach($_REQUEST['data'] AS $datum)
		{
			if(!$Tag = $className::getByID($datum['TagID']))
			{
				return static::throwNotFoundError();
			}
			
			$TagItem = $Tag->assignItem($datum['ContextClass'], $datum['ContextID']);
			
			if($TagItem->isNew)
			{
				$results[] = $TagItem;
			}
		}
		
		
		return static::respond($className::$pluralNoun.'Saved', array(
			'success' => true
			,'data' => $results
		));
	}
}