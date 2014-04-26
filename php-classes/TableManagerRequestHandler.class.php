<?php

class TableManagerRequestHandler extends RequestHandler
{
    static public $classFilters = array(
        '/^(Dwoo|Sabre|PHPUnit)[\\\\_]/',
        '/^getID3/',
        '/(Trait|Interface|Test)$/'
    );
    
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
			
			case 'ext-model':
			{
				return static::handleExtModelRequest();
			}
			
			case 'ext-columns':
			{
				return static::handleExtColumnsRequest();
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
      
		foreach (Emergence_FS::findFiles('\.php$', true, 'php-classes') AS $classNode) {
			if ($classNode->Type != 'application/php') {
				continue;
			}
            
            $classPath = $classNode->getFullPath(null, false);
            array_shift($classPath);

			$className = preg_replace('/(\.class)?\.php$/i', '', join('\\', $classPath));
            
            foreach (static::$classFilters AS $pattern) {
                if (preg_match($pattern, $className)) {
                    continue 2;
                }
            }

			if (is_subclass_of($className, 'ActiveRecord') && !in_array($className, $recordClasses)) {
				$recordClasses[] = $className;
			}
		}
        
        natsort($recordClasses);

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
	
	
	static public function handleExtModelRequest()
	{
	
		if(empty($_REQUEST['class']) || !is_subclass_of($_REQUEST['class'], 'ActiveRecord'))
		{
			return static::throwInvalidRequestError();
		}

		return static::respond('ext-model', array(
			'data' => Sencha\CodeGenerator::getRecordModel($_REQUEST['class'])
			,'class' => $_REQUEST['class']
		));
	}

	static public function handleExtColumnsRequest()
	{
	
		if(empty($_REQUEST['class']) || !is_subclass_of($_REQUEST['class'], 'ActiveRecord'))
		{
			return static::throwInvalidRequestError();
		}

		return static::respond('ext-columns', array(
			'data' => Sencha\CodeGenerator::getRecordColumns($_REQUEST['class'])
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