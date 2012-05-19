<?php

class PasswordToken extends Token
{

	static public $emailTemplate = 'register/passwordToken.email';
	static public $formTemplate = 'register/passwordForm';
	static public $emailSubject = 'The password recovery link you requested';


	public function handleRequest($data)
	{
		parent::handleRequest($data);

		if(empty($data['Password']))
		{
			throw new Exception('Enter a new password for your account');
		}
		elseif($data['Password'] != $data['PasswordConfirm'])
		{
			throw new Exception('Enter your new password twice for confirmation');
		}

		$this->Creator->Password = call_user_func(User::$passwordHasher, $data['Password']);
		$this->Creator->save();
		
		// set used
		$this->Used = time();
		$this->save();
	
		return RequestHandler::respond('register/passwordRecovered');
	}

}