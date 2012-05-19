<?php



abstract class RequestHandler
{
	// configurables
	public static $responseMode = 'html';
	
	
	// abstract methods
	abstract public static function handleRequest();
	
	// static properties
	protected static $_path;
	protected static $_parameters;
	protected static $_options = array();
	

	// protected static methods

	protected static function setPath($path = null)
	{
		static::$_path = isset($path) ? $path : Site::$pathStack;
	}
	
	protected static function setOptions($options)
	{
		static::$_options = isset(self::$_options) ? array_merge(static::$_options, $options) : $options;
	}
	
	
	protected static function peekPath()
	{
		if(!isset(static::$_path)) static::setPath();
		return count(static::$_path) ? static::$_path[0] : false;
	}

	protected static function shiftPath()
	{
		if(!isset(static::$_path)) static::setPath();
		return array_shift(static::$_path);
	}

	protected static function getPath()
	{
		if(!isset(static::$_path)) static::setPath();
		return static::$_path;
	}
	
	protected static function unshiftPath($string)
	{
		if(!isset(static::$_path)) static::setPath();
		return array_unshift(static::$_path, $string);
	}
	
	// this doesn't seem to work, would be cool if it could
	protected static function catchParentResponse(Closure $function)
	{
		// store response mode
		$origResponseMode = static::$responseMode;
		
		// execute function and capture response
		static::$responseMode = 'return';
		$response = $function();

		
		// restore original response mode
		static::$responseMode = $origResponseMode;
		
		return $response;
	}
	
	static public function respond($responseID, $responseData = array(), $responseMode = false)
	{
		header('X-Response-ID: '.$responseID);
	
		switch($responseMode ? $responseMode : static::$responseMode)
		{
			case 'json':
				return JSON::translateAndRespond($responseData);
				
			case 'csv':
			{
				if(is_array($responseData['data']))
				{
					header('Content-Type: text/csv');
					header('Content-Disposition: attachment; filename="'.str_replace('"', '', $responseID).'.csv"');
					print(CSV::fromRecords($responseData['data']));
				}
				elseif($responseID == 'error')
				{
					print ($responseData['message']);
				}
				else
				{
					print 'Unable to render data to CSV: '.$responseID;
				}
				exit();
			}

			case 'html':
				$responseData['responseID'] = $responseID;
				return TemplateResponse::respond($responseID, $responseData);

			case 'xml':
				header('Content-Type: text/xml');
				return TemplateResponse::respond($responseID, $responseData);
				
			case 'return':
				return array(
					'responseID' => $responseID
					,'data' => $responseData
				);

			default:
				die('Invalid response mode');
		}
	}
	
	public static function throwUnauthorizedError($message = 'You do not have authorization to access this resource')
	{
		if(!$GLOBALS['Session']->Person)
			$GLOBALS['Session']->requireAuthentication();
	
		header('HTTP/1.0 403 Forbidden');
		$args = func_get_args();
		$args[0] = $message;
		return call_user_func_array(array(get_called_class(), 'throwError'), $args);
	}

	public static function throwAPIUnauthorizedError($message = 'You do not have authorization to access this resource')
	{
		header('HTTP/1.0 403 Forbidden');
		switch(static::$responseMode)
		{
			case 'json':
			default:
				JSON::respond(array(
					'success' => false
					,'message' => $message
				));
		}
	}

	public static function throwNotFoundError($message = 'Page not found')
	{
		header('HTTP/1.0 404 Not Found');
		$args = func_get_args();
		$args[0] = $message;
		return call_user_func_array(array(get_called_class(), 'throwError'), $args);
	}
	
	public static function throwValidationError(RecordValidationException $e, $message = 'There were errors validating your submission')
	{
		return static::respond('validationError', array(
			'success' => false
			,'message' => $message
			,'validationErrors' => $e->recordObject->validationErrors
			,'recordClass' => get_class($e->recordObject)
			,'recordID' => $e->recordObject->ID
		));
	}
	
	public static function throwInvalidRequestError($message = 'You did not supply the needed parameters correctly')
	{
		return static::throwError($message);
	}

	public static function throwError($message)
	{
		$args = func_get_args();

		return static::respond('error', array(
			'success' => false
			,'message' => vsprintf($message, array_slice($args, 1))
		));
	}

	
	// public static methods
	public static function getOption($optionName)
	{
		return isset(static::$_options[$optionName]) ? static::$_options[$optionName] : null;
	}
	
	public static function getOptions()
	{
		return static::$_options;
	}
}
