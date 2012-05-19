<?php

class TemplatePreviewRequestHandler
{

	static public function handleRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		TemplateResponse::respond(implode('/',Site::$pathStack));
	}

}