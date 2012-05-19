<?php



 class Note extends ActiveRecord
{

	// ActiveRecord configuration
	static $tableName = 'notes';
	
	static $fields = array(
		'Body' => array(
			'type' => 'clob'
		)
	);
	

}