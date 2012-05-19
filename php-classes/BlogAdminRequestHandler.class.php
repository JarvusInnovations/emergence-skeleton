<?php
class BlogAdminRequestHandler extends RequestHandler {
	static public function handleRequest() {
		$GLOBALS['Session']->requireAccountLevel('Staff');
		
		static::respond('blog/admin/home');
	}
}