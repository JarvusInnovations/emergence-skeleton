<?php

namespace Emergence\Mailer;

class Mailer
{
	static public $defaultImplementation = '\Emergence\Mailer\PHPMailer';
	static public $defaultFrom;
	
	static public function __callStatic($name, $args)
	{
		return call_user_func_array(array(static::$defaultImplementation, $name), $args);
	}
}