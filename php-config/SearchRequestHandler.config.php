<?php

if ($GLOBALS['Session']->hasAccountLevel('User')) {
	SearchRequestHandler::$searchClasses['User'] = array(
		'fields' => array(
			array(
				'field' => 'FirstName'
				,'method' => 'like'
			), array(
				'field' => 'LastName'
				,'method' => 'like'
			), array(
				'field' => 'Username'
				,'method' => 'like'
			), array(
				'field' => 'FullName'
				,'method' => 'sql'
				,'sql' => 'CONCAT(FirstName," ",LastName) = "%s"'
			)
		)
		,'conditions' => array('AccountLevel != "Deleted"')
	);
}

