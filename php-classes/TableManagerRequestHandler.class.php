<?php


class TableManagerRequestHandler extends RequestHandler
{


	static public function handleRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');
		
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
	
		switch($handle = static::shiftPath())
		{
			case '':
			case false:
			case 'classes':
			{
				return static::handleClassesRequest();
			}
			
			case 'sql':
			{
				return static::handleSQLRequest();
			}
			
			case 'metadata':
			{
				return static::handleMetadataRequest();
			}
			
			case 'column_model':
			{
				return static::handleColumnModelRequest();
			}
			
			case 'index':
			{
				return static::handleManagerRequest();
			}
			
			case 'renest':
			{
				return static::handleRenestRequest();

			}
			
			default:
			{
				return static::throwNotFoundError();
			}
		}
	}


	static public function handleManagerRequest()
	{
		return static::respond('manager');
	}


	static public function handleClassesRequest()
	{
		// discover activerecord classes
		$recordClasses = array();
		
		$localClasses = SiteCollection::getOrCreateCollection('php-classes');
		$parentClasses = SiteCollection::getOrCreateCollection('php-classes', null, true);

		foreach(array_merge($localClasses->getChildren(),$parentClasses->getChildren()) AS $classNode)
		{
			if($classNode->Type != 'application/php')
				continue;
			
			$className = preg_replace('/\.class\.php$/i', '', $classNode->Handle);

			if(is_subclass_of($className, 'ActiveRecord') && !in_array($className, $recordClasses))
			{
				$recordClasses[] = $className;
			}
		}

		return static::respond('classes', array(
			'classes' => $recordClasses
		));
	}

	static public function handleSQLRequest()
	{
		if(empty($_REQUEST['class']) || !is_subclass_of($_REQUEST['class'], 'ActiveRecord'))
		{
			return static::throwInvalidRequestError();
		}
		
		// handle execute
		if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_REQUEST['sql']))
		{
			$sql = preg_replace('/^--.*/m', '', $_REQUEST['sql']);
		
			if(!$success = DB::getMysqli()->multi_query($sql))
			{
				$error = DB::getMysqli()->error;
			}
			return static::respond('sqlExecuted', array(
				'query' => $_REQUEST['sql']
				,'class' => $_REQUEST['class']
				,'success' => $success
				,'error' => isset($error) ? $error : null
			));
		}
	
		return static::respond('sql', array(
			'query' => SQL::getCreateTable($_REQUEST['class'])
			,'class' => $_REQUEST['class']
		));
	}
	
	
	static public function handleMetadataRequest()
	{
	
		if(empty($_REQUEST['class']) || !is_subclass_of($_REQUEST['class'], 'ActiveRecord'))
		{
			return static::throwInvalidRequestError();
		}

		return static::respond('metadata', array(
			'data' => ExtJS::getRecordMetadata($_REQUEST['class'])
			,'class' => $_REQUEST['class']
		));
	}

	static public function handleColumnModelRequest()
	{
	
		if(empty($_REQUEST['class']) || !is_subclass_of($_REQUEST['class'], 'ActiveRecord'))
		{
			return static::throwInvalidRequestError();
		}

		return static::respond('column_model', array(
			'data' => ExtJS::getColumnModelConfig($_REQUEST['class'])
			,'class' => $_REQUEST['class']
		));
	}

	static public function handleRenestRequest()
	{
	
		if(empty($_REQUEST['class']) || !is_subclass_of($_REQUEST['class'], 'ActiveRecord'))
		{
			return static::throwInvalidRequestError();
		}
		
		NestingBehavior::repairTable($_REQUEST['class']::$tableName);

		return static::respond('message', array(
			'message' => 'Renesting complete'
		));
	}


}