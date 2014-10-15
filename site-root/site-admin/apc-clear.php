<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

if($_SERVER['REQUEST_METHOD'] == 'POST')
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

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Clear APC</title>
    <style>
        * {
            font-size: xx-large;
            text-align: center;
        }

        input {
            cursor: pointer;
            margin: 1em;
            width: 3em;
        }
    </style>
</head>
<body>
<form method="POST">
    <p><em>Clear APC</em></p>
	<input type="submit" name="target" value="System">
	<input type="submit" name="target" value="User">
	<input type="submit" name="target" value="All">
</form>
</body>
</html>