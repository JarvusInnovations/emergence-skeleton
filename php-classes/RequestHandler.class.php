<?php

abstract class RequestHandler
{
    // configurables
    public static $responseMode = 'html';
    public static $userResponseModes = array(); // array of responseModes that can be selected by the user, with key optionally set to a MIME Type
    
    
    // static properties
    protected static $_path;
	

	// protected static methods
	protected static function setPath($path = null)
	{
		static::$_path = isset($path) ? $path : Site::$pathStack;
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

    public static function getResponseMode()
    {
        if (!empty($_GET['format']) && in_array($_GET['format'], static::$userResponseModes)) {
            return $_GET['format'];
        } elseif (!empty($_SERVER['HTTP_ACCEPT']) && array_key_exists($_SERVER['HTTP_ACCEPT'], static::$userResponseModes)) {
            return static::$userResponseModes[$_SERVER['HTTP_ACCEPT']];
        } else {
		    return static::$responseMode;
        }
    }

	static public function respond($responseID, $responseData = array(), $responseMode = false)
	{
		if (!$responseMode) {
            $responseMode = static::getResponseMode();
		}

		if ($responseMode != 'return') {
			header('X-Response-ID: '.$responseID);
		}
	
		switch($responseMode)
		{
			case 'json':
				return static::respondJson($responseID, $responseData);
				
			case 'csv':
                return static::respondCsv($responseID, $responseData);
            
            case 'pdf':
                return static::respondPdf($responseID, $responseData);

    		case 'xml':
                return static::respondXml($responseID, $responseData);

			case 'html':
                return static::respondHtml($responseID, $responseData);
				
			case 'return':
				return array(
					'responseID' => $responseID
					,'data' => $responseData
				);

			default:
				throw new Exception('Invalid response mode');
		}
	}
    
    static public function respondJson($responseID, $responseData = array())
    {
        return JSON::translateAndRespond($responseData, !empty($_GET['summary']), !empty($_GET['include']) ? $_GET['include'] : null);
    }
    
    static public function respondCsv($responseID, $responseData = array())
    {
        if (!empty($_REQUEST['downloadToken'])) {
            setcookie('downloadToken', $_REQUEST['downloadToken'], time()+300, '/');
        }
        
		if (is_array($responseData['data'])) {
            return CSV::respond($responseData['data'], $responseID, !empty($_GET['columns']) ? $_GET['columns'] : null);
		} elseif ($responseID == 'error') {
			print($responseData['message']);
		} else {
			print 'Unable to render data to CSV: '.$responseID;
		}
		exit();
    }
    
    static public function respondPdf($responseID, $responseData = array())
    {
        if (!empty($_REQUEST['downloadToken'])) {
            setcookie('downloadToken', $_REQUEST['downloadToken'], time()+300, '/');
        }
        
		$tmpPath = tempnam('/tmp', 'e_pdf_');

		file_put_contents($tmpPath.'.html', Emergence\Dwoo\Engine::getSource("$responseID.pdf", $responseData));
        
        header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="'.str_replace('"', '', $responseID).'.pdf"');
        
		exec("/usr/local/bin/wkhtmltopdf \"$tmpPath.html\" \"$tmpPath.pdf\"");
        
        if (!file_exists("$tmpPath.pdf")) {
            header('HTTP/1.0 501 Not Implemented');
            die('Unable to generate PDF, check that this system has wkhtmltopdf installed');
        }
        
        readfile($tmpPath.'.pdf');
        exit();
    }
    
    static public function respondXml($responseID, $responseData = array())
    {
		header('Content-Type: text/xml');
		return Emergence\Dwoo\Engine::respond($responseID, $responseData);
    }
    
    static public function respondHtml($responseID, $responseData = array())
    {
		header('Content-Type: text/html; charset=utf-8');
		$responseData['responseID'] = $responseID;
		return Emergence\Dwoo\Engine::respond($responseID, $responseData);
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

    public static function throwServerError($message = 'An unknown problem has prevented the server from completing your request')
	{
		header('HTTP/1.0 500 Internal Server Error');
		$args = func_get_args();
		$args[0] = $message;
		return call_user_func_array(array(get_called_class(), 'throwError'), $args);
	}
	
	public static function throwValidationError(RecordValidationException $e, $message = 'There were errors validating your submission')
	{
        header('HTTP/1.0 400 Bad Request');
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
    	header('HTTP/1.0 400 Bad Request');
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
}