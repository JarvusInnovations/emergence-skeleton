<?php

class TemplatePreviewRequestHandler
{
	public static function handleRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		Emergence\Dwoo\Engine::respond(implode('/', Site::$pathStack));
	}
}