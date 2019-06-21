<?php

ContactRequestHandler::$validators[] = [
    'field' => 'Name',
    'validator' => 'string',
    'required' => true
];


ContactRequestHandler::$validators[] = [
    'field' => 'Email',
    'validator' => 'email',
    'required' => true
];


ContactRequestHandler::$validators[] = [
    'field' => 'Phone',
    'validator' => 'phone',
    'required' => false
];


ContactRequestHandler::$validators[] = [
    'field' => 'Message',
    'validator' => 'string_multiline',
    'required' => true
];
