<?php
    namespace Emergence\Developer\Tools;

class RequestHandler extends \RequestHandler
{

	public static $section = 'home';
	public static function handleRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		$action ? $action : $action = static::shiftPath();

		static::$section = $action;

		switch(static::$section)
		{
			case 'git':
				return Git\RequestHandler::handleRequest();

			case 'tools':
				return Tools\RequestHandler::handleRequest();

			default:
				return static::handleHomeRequest();
		}

		return static::throwNotFoundError();
	}

	public static function respond($responseID, $responseData = [], $responseMode = false) {
		return parent::respond($responseID, array_merge($responseData,[

		]),$responseMode);
	}

	public static function handleHomeRequest()
	{
		static::respond('home', [
			'Mounts' => SysInfo::mounts(),
			'CPUInfo' => SysInfo::CPUInfo(),
			'MemoryInfo'=> SysInfo::MemoryInfo()
		]);
	}

	public static function throwNotFoundError($message = 'Page not found')
	{
		header('HTTP/1.0 404 Not Found');

		static::respond('notfound');
	}
}