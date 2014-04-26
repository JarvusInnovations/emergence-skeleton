<?php
$GLOBALS['Session']->requireAccountLevel('Developer');

if($_SERVER['REQUEST_METHOD'] == 'POST')
{    		
	switch($action = array_shift(Site::$pathStack))
	{
		case 'test-exception':
		{
			throw new Exception('This is a test exception');
		}
		
		case 'templates-clear':
		{
			$templateDir = Emergence\Dwoo\Engine::$pathCompile . '/' . Site::getConfig('handle');
			
			exec("find $templateDir -name \"*.d*.php\" -delete");
			apc_clear_cache();
#			apc_clear_cache('user');
			die('Templates cleared');
		}
	
	
		case 'parent-clear':
		{
			DB::nonQuery(
				'DELETE FROM `%s` WHERE CollectionID IN (SELECT ID FROM `%s` WHERE Site != "Local")'
				,array(
					SiteFile::$tableName
					,SiteCollection::$tableName
				)
			);
			apc_clear_cache('user');
			die('Cleared '.DB::affectedRows().' cached files');
		}
	
	
		case 'parent-clear-all':
		{
			DB::nonQuery(
				'DELETE FROM `%s` WHERE CollectionID IN (SELECT ID FROM `%s` WHERE Site != "Local")'
				,array(
					SiteFile::$tableName
					,SiteCollection::$tableName
				)
			);
			
			print('Cleared '.DB::affectedRows().' cached files<br>'.PHP_EOL);
			
			DB::nonQuery(
				'DELETE FROM `%s` WHERE Site != "Local"'
				,array(
					SiteCollection::$tableName
				)
			);
			
			die('Cleared '.DB::affectedRows().' cached collections');
		}
		
		case 'apc':
		{
			if($_REQUEST['target'] != 'System')
			{
				apc_clear_cache('user');
				print "Cleared user cache.<br>\n";
			}
			
			if($_REQUEST['target'] != 'User')
			{
				apc_clear_cache();
				print "Cleared system cache.<br>\n";
			}
			
			die('APC cache cleared.');
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
	
	<form method="POST" action="/admin/apc">
		<fieldset>
			<legend>Clear APC</legend>
			<input type="submit" name="target" value="System">
			<input type="submit" name="target" value="User">
			<input type="submit" name="target" value="All">
		</fieldset>
	</form>
    
	<form method="GET" action="/table_manager">
		<input type="submit" value="Open table manager">
	</form>

    <h3>Destructive operations &mdash; Advanced users only</h3>
    <form method="POST" action="/admin/parent-clear">
		<input type="submit" value="Clear Parent Site Cache">
	</form>

	<form method="POST" action="/admin/parent-clear-all">
		<input type="submit" value="Clear Parent Site Cache (nuke collections too)">
	</form>

</body>
</html>