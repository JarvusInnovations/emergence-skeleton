<?php

abstract class RecordsRequestHandler extends RequestHandler
{

	// configurables
	static public $recordClass;
	static public $accountLevelRead = false;
	static public $accountLevelBrowse = 'Staff';
	static public $accountLevelWrite = 'Staff';
	static public $accountLevelAPI = false;
	static public $browseOrder = false;
	static public $browseConditions = false;
	static public $browseLimitDefault = false;
	static public $editableFields = false;
	static public $searchConditions = false;
	
	static public $calledClass = __CLASS__;
	static public $responseMode = 'html';
	
	static public function handleRequest()
	{
		// save static class
		static::$calledClass = get_called_class();
	
		// handle JSON requests
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
		
		return static::handleRecordsRequest();
	}


	static public function handleRecordsRequest($action = false)
	{
		switch($action ? $action : $action = static::shiftPath())
		{
			case 'save':
			{
				return static::handleMultiSaveRequest();
			}
			
			case 'destroy':
			{
				return static::handleMultiDestroyRequest();
			}
			
			case 'create':
			{
				return static::handleCreateRequest();
			}
			
			case '':
			case false:
			{
				return static::handleBrowseRequest();
			}

			default:
			{
				if($Record = static::getRecordByHandle($action))
				{
					if(!static::checkReadAccess($Record))
					{
						return static::throwUnauthorizedError();
					}

					return static::handleRecordRequest($Record);
				}
				else
				{
					return static::throwRecordNotFoundError($action);
				}
			}
		}
	}
	
	static public function getRecordByHandle($handle)
	{
		$className = static::$recordClass;
		
		if(method_exists($className, 'getByHandle'))
			return $className::getByHandle($handle);
		else
			return null;
	}
	
	static public function handleQueryRequest($query, $conditions = array(), $options = array(), $responseID = null, $responseData = array())
	{
		$terms = preg_split('/\s+/', $query);
		
		$options = Site::prepareOptions($options, array(
			'limit' =>  !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : static::$browseLimitDefault
			,'offset' => !empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset']) ? $_REQUEST['offset'] : false
			,'order' => array('searchScore DESC')
		));

		$select = array('*');
		$having = array();
		$matchers = array();

		foreach($terms AS $term)
		{
			$n = 0;
			$qualifier = 'any';
			$split = explode(':', $term, 2);
			
			if(count($split) == 2)
			{
				$qualifier = strtolower($split[0]);
				$term = $split[1];
			}
			
			foreach(static::$searchConditions AS $k => $condition)
			{
				if(!in_array($qualifier, $condition['qualifiers']))
					continue;

				$matchers[] = array(
					'condition' => sprintf($condition['sql'], DB::escape($term))
					,'points' => $condition['points']
				);
				
				$n++;
			}
			
			if($n == 0)
			{
				throw new Exception('Unknown search qualifier: '.$qualifier);
			}
		}
		
		$select[] = join('+', array_map(function($c) {
			return sprintf('IF(%s, %u, 0)', $c['condition'], $c['points']);
		}, $matchers)) . ' AS searchScore';
		
		$having[] = 'searchScore > 1';
	
		$className = static::$recordClass;

		return static::respond(
			isset($responseID) ? $responseID : static::getTemplateName($className::$pluralNoun)
			,array_merge($responseData, array(
				'success' => true
				,'data' => $className::getAllByQuery(
					'SELECT %s FROM `%s` WHERE (%s) %s %s %s'
					,array(
						join(',',$select)
						,$className::$tableName
						,$conditions ? join(') AND (',$className::mapConditions($conditions)) : '1'
						,count($having) ? 'HAVING ('.join(') AND (', $having).')' : ''
						,count($options['order']) ? 'ORDER BY '.join(',', $options['order']) : ''
						,$options['limit'] ? sprintf('LIMIT %u,%u',$options['offset'],$options['limit']) : ''
					)
				)
				,'query' => $query
				,'conditions' => $conditions
			    ,'total' => DB::foundRows()
			    ,'limit' => $options['limit']
			    ,'offset' => $options['offset']
			))
		);
	}


	static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
	{
		if(!static::checkBrowseAccess(func_get_args()))
		{
			return static::throwUnauthorizedError();
		}
			
		if(static::$browseConditions)
		{
			if(!is_array(static::$browseConditions))
				static::$browseConditions = array(static::$browseConditions);
			$conditions = array_merge(static::$browseConditions, $conditions);
		}
		
		$limit = !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? $_REQUEST['limit'] : static::$browseLimitDefault;
		$offset = !empty($_REQUEST['offset']) && is_numeric($_REQUEST['offset']) ? $_REQUEST['offset'] : false;
		
		$options = Site::prepareOptions($options, array(
			'limit' =>  $limit
			,'offset' => $offset
			,'order' => static::$browseOrder
		));

		// handle query search
		if(!empty($_REQUEST['q']) && static::$searchConditions)
		{
			return static::handleQueryRequest($_REQUEST['q'], $conditions, array('limit' => $limit, 'offset' => $offset), $responseID, $responseData);
		}

		$className = static::$recordClass;

		return static::respond(
			isset($responseID) ? $responseID : static::getTemplateName($className::$pluralNoun)
			,array_merge($responseData, array(
				'success' => true
				,'data' => $className::getAllByWhere($conditions, $options)
				,'conditions' => $conditions
			    ,'total' => DB::foundRows()
			    ,'limit' => $options['limit']
			    ,'offset' => $options['offset']
			))
		);
	}


	static public function handleRecordRequest(ActiveRecord $Record, $action = false)
	{
	
		switch($action ? $action : $action = static::shiftPath())
		{
			case '':
			case false:
			{
				$className = static::$recordClass;
				
				return static::respond(static::getTemplateName($className::$singularNoun), array(
					'success' => true
					,'data' => $Record
				));
			}
			
			case 'edit':
			{
				return static::handleEditRequest($Record);
			}
			
			case 'delete':
			{
				return static::handleDeleteRequest($Record);
			}
		
			default:
			{
				return static::onRecordRequestNotHandled($Record, $action);
			}
		}
	}
	
	static protected function onRecordRequestNotHandled(ActiveRecord $Record, $action)
	{
		return static::throwNotFoundError();
	} 



	static public function handleMultiSaveRequest()
	{		
		if(static::$responseMode == 'json' && in_array($_SERVER['REQUEST_METHOD'], array('POST','PUT')))
		{
			$_REQUEST = JSON::getRequestData();
		}
				
		if(empty($_REQUEST['data']) || !is_array($_REQUEST['data']))
		{
			return static::throwInvalidRequestError('Save expects "data" field as array of record deltas');
		}
		
		$className = static::$recordClass;
		$results = array();
		$failed = array();

		foreach($_REQUEST['data'] AS $datum)
		{
			// get record
			if(empty($datum['ID']))
			{
				$Record = new $className::$defaultClass();
				static::onRecordCreated($Record, $datum);
			}
			else
			{
				if(!$Record = $className::getByID($datum['ID']))
				{
					return static::throwRecordNotFoundError($datum['ID']);
				}
			}
			
			// check write access
			if(!static::checkWriteAccess($Record))
			{
				$failed[] = array(
					'record' => $datum
					,'errors' => 'Write access denied'
				);
				continue;
			}
 			
			// apply delta
			static::applyRecordDelta($Record, $datum);

			// call template function
			static::onBeforeRecordValidated($Record, $datum);

			// try to save record
			try
			{
				// call template function
				static::onBeforeRecordSaved($Record, $datum);

				$Record->save();
				$results[] = (!$Record::_fieldExists('Class') || get_class($Record) == $Record->Class) ? $Record : $Record->changeClass();
				
				// call template function
				static::onRecordSaved($Record, $datum);
			}
			catch(RecordValidationException $e)
			{
				$failed[] = array(
					'record' => $Record->data
					,'validationErrors' => $Record->validationErrors
				);
			}
		}
		
		
		return static::respond(static::getTemplateName($className::$pluralNoun).'Saved', array(
			'success' => count($results) || !count($failed)
			,'data' => $results
			,'failed' => $failed
		));
	}
	
	
	static public function handleMultiDestroyRequest()
	{
		
		if(static::$responseMode == 'json' && in_array($_SERVER['REQUEST_METHOD'], array('POST','PUT','DELETE')))
		{
			$_REQUEST = JSON::getRequestData();
		}
				
		if(empty($_REQUEST['data']) || !is_array($_REQUEST['data']))
		{
			return static::throwInvalidRequestError('Handler expects "data" field as array');
		}
		
		$className = static::$recordClass;
		$results = array();
		$failed = array();
		
		foreach($_REQUEST['data'] AS $datum)
		{
			// get record
			if(is_numeric($datum))
			{
				$recordID = $datum;
			}
			elseif(!empty($datum['ID']) && is_numeric($datum['ID']))
			{
				$recordID = $datum['ID'];
			}
			else
			{
				$failed[] = array(
					'record' => $datum
					,'errors' => 'ID missing'
				);
				continue;
			}

			if(!$Record = $className::getByID($recordID))
			{
				$failed[] = array(
					'record' => $datum
					,'errors' => 'ID not found'
				);
				continue;
			}
			
			// check write access
			if(!static::checkWriteAccess($Record))
			{
				$failed[] = array(
					'record' => $datum
					,'errors' => 'Write access denied'
				);
				continue;
			}
		
			// destroy record
			if($Record->destroy())
			{
				$results[] = $Record;
			}
		}
		
		return static::respond(static::getTemplateName($className::$pluralNoun).'Destroyed', array(
			'success' => count($results) || !count($failed)
			,'data' => $results
			,'failed' => $failed
		));
	}


	static public function handleCreateRequest(ActiveRecord $Record = null)
	{
		// save static class
		static::$calledClass = get_called_class();

		if(!$Record)
		{
			$className = static::$recordClass;
			$Record = new $className::$defaultClass();
		}
		
		// call template function
		static::onRecordCreated($Record, $_REQUEST);

		return static::handleEditRequest($Record);
	}

	static public function handleEditRequest(ActiveRecord $Record)
	{
		$className = static::$recordClass;

		if(!static::checkWriteAccess($Record))
		{
			return static::throwUnauthorizedError();
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// apply delta
			static::applyRecordDelta($Record, $_REQUEST);
			
			// call template function
			static::onBeforeRecordValidated($Record, $_REQUEST);

			// validate
			if($Record->validate())
			{
				// call template function
				static::onBeforeRecordSaved($Record, $_REQUEST);
				
				// save session
				$Record->save();
				
				// call template function
				static::onRecordSaved($Record, $_REQUEST);
				
				// fire created response
				$responseID = static::getTemplateName($className::$singularNoun).'Saved';
				$responseData = static::getEditResponse($responseID, array(
					'success' => true
					,'data' => $Record
				));
				return static::respond($responseID, $responseData);
			}
			
			// fall through back to form if validation failed
		}
	
		$responseID = static::getTemplateName($className::$singularNoun).'Edit';
		$responseData = static::getEditResponse($responseID, array(
			'success' => false
			,'data' => $Record
		));
	
		return static::respond($responseID, $responseData);
	}


	static public function handleDeleteRequest(ActiveRecord $Record)
	{
		$className = static::$recordClass;

		if(!static::checkWriteAccess($Record))
		{
			return static::throwUnauthorizedError();
		}
	
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$Record->destroy();

			// fire created response
			return static::respond(static::getTemplateName($className::$singularNoun).'Deleted', array(
				'success' => true
				,'data' => $Record
			));
		}
	
		return static::respond('confirm', array(
			'question' => 'Are you sure you want to delete this '.$className::$singularNoun.'?'
			,'data' => $Record
		));
	}
	


	static protected function getTemplateName($noun)
	{
		return preg_replace_callback('/\s+([a-zA-Z])/', function($matches) { return strtoupper($matches[1]); }, $noun);
	}
	
	
	static public function respond($responseID, $responseData = array(), $responseMode = false)
	{
		// default to static property
		if(!$responseMode)
		{
			$responseMode = static::$responseMode;
		}
		
		// check access for API response modes
		if($responseMode != 'html' && $responseMode != 'return')
		{
			if(!static::checkAPIAccess($responseID, $responseData, $responseMode))
			{
				return static::throwAPIUnauthorizedError();
			}
		}
	
		return parent::respond($responseID, $responseData, $responseMode);
	}
	
	static protected function applyRecordDelta(ActiveRecord $Record, $data)
	{
		if(static::$editableFields)
		{
			$Record->setFields(array_intersect_key($data, array_flip(static::$editableFields)));
		}
		else
		{
			return $Record->setFields($data);
		}
	}
	
	// event template functions
	static protected function onRecordCreated(ActiveRecord $Record, $data)
	{
	}
	static protected function onBeforeRecordValidated(ActiveRecord $Record, $data)
	{
	}
	static protected function onBeforeRecordSaved(ActiveRecord $Record, $data)
	{
	}
	static protected function onRecordSaved(ActiveRecord $Record, $data)
	{
	}
	
	static protected function getEditResponse($responseID, $responseData)
	{
		return $responseData;
	}
	
	// access control template functions
	static public function checkBrowseAccess($arguments)
	{
		if(static::$accountLevelBrowse)
		{
			return $GLOBALS['Session']->hasAccountLevel(static::$accountLevelBrowse);
		}
		
		return true;
	}

	static public function checkReadAccess(ActiveRecord $Record)
	{
		if(static::$accountLevelRead)
		{
			return $GLOBALS['Session']->hasAccountLevel(static::$accountLevelRead);
		}
		
		return true;
	}
	
	static public function checkWriteAccess(ActiveRecord $Record)
	{
		if(static::$accountLevelWrite)
		{
			return $GLOBALS['Session']->hasAccountLevel(static::$accountLevelWrite);
		}
		
		return true;
	}
	
	static public function checkAPIAccess($responseID, $responseData, $responseMode)
	{
		if(static::$accountLevelAPI)
		{
			return $GLOBALS['Session']->hasAccountLevel(static::$accountLevelAPI);
		}
		
		return true;
	}
	
	
	static protected function throwRecordNotFoundError($handle, $message = 'Record not found')
	{
		return static::throwNotFoundError($message);
	}
	

}