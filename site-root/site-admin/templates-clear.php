<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

if($_SERVER['REQUEST_METHOD'] == 'POST')
{			
	$templateDir = TemplateResponse::$pathCompile . $_SERVER['SITE_ROOT'];
	
	exec("find $templateDir -name \"*.d16.php\" -delete");
	die('Templates cleared');
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Clear Templates Cache</title>
    <style>
        * {
            font-size: xx-large;
            text-align: center;
        }

        input {
            cursor: pointer;
            margin: 1em;
        }
    </style>
</head>
<body>
<form method="POST">
	<input type="submit" value="Clear Templates Cache">
</form>
</body>
</html>