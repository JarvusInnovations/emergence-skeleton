<?php
$GLOBALS['Session']->requireAccountLevel('Developer');

if($_SERVER['REQUEST_METHOD'] == 'POST')
{			
	switch($action = array_shift(Site::$pathStack))
	{
		case 'templates-clear':
		{
			$templateDir = TemplateResponse::$pathCompile . $_SERVER['SITE_ROOT'];
			
			exec("find $templateDir -name \"*.d16.php\" -delete");
			die('Templates cleared');
		}
	
	
		case 'parent-clear':
		{
			DB::nonQuery(
				'DELETE FROM `%s` WHERE CollectionID IN (SELECT ID FROM `%s` WHERE SiteID != %u)'
				,array(
					SiteFile::$tableName
					,SiteCollection::$tableName
					,Site::getSiteID()
				)
			);
			
			die('Cleared '.DB::affectedRows().' cached files');
		}
	}
}
	
?>
<html>
<head><title>Emergence Administrator</title></head>
<body>

	<form method="POST" action="/admin/templates-clear">
		<input type="submit" value="Clear Templates Cache">
	</form>

	<form method="POST" action="/admin/parent-clear">
		<input type="submit" value="Clear Parent Site Cache">
	</form>
	
	<form method="GET" action="/table_manager">
		<input type="submit" value="Create table schema">
	</form>

</body>
</html>