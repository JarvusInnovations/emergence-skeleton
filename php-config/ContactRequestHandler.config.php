<?php


//ContactRequestHandler::$emailTo = 'hello@example.com'; // uncomment and set to receive email alerts


ContactRequestHandler::$validators[] = array(
    'field' => 'Name'
	,'validator' => 'string'
    ,'required' => true
);


ContactRequestHandler::$validators[] = array(
    'field' => 'Email'
	,'validator' => 'email'
    ,'required' => true
);


ContactRequestHandler::$validators[] = array(
	'field' => 'Phone'
	,'validator' => 'phone'
    ,'required' => true
);


ContactRequestHandler::$validators[] = array(
	'field' => 'Message'
	,'validator' => 'string_multiline'
    ,'required' => true
);