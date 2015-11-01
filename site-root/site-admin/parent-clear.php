<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Clear Parent Site Cache</title>
    <style>
        * {
            font-size: xx-large;
            text-align: center;
        }

        input {
            cursor: pointer;
            margin: 1em;
        }

        em strong {
            color: #c00;
        }
    </style>
</head>
<body>
<form method="POST">
	<p><em><strong>Warning:</strong> destructive operation.<br>For advanced users only.</em></p>
	<input type="submit" value="Clear Parent Site Cache">
</form>
</body>
</html>