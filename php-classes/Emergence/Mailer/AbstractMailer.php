<?php

namespace Emergence\Mailer;

abstract class AbstractMailer implements IMailer
{
    static public function getDefaultFrom()
	{
		return Mailer::$defaultFrom ? Mailer::$defaultFrom : 'support@'.$_SERVER['HTTP_HOST'];
	}
	
	static public function sendFromTemplate($to, $template, $data = array(), $options = array())
	{
		$email = static::renderTemplate($template, $data);
		
		return static::send($to, $email['subject'], $email['body'], $email['from'], $options);
	}
	
	static public function renderTemplate($template, $data = array())
	{
		$body = trim(\Emergence\Dwoo\Engine::getSource($template.'.email', $data));
		$templateVars = \Emergence\Dwoo\Engine::getInstance()->scope;
		
		return array(
			'from' => $templateVars['from'] ? $templateVars['from'] : false
			,'subject' => $templateVars['subject'] ? $templateVars['subject'] : false
			,'body' => $body
		);
	}
}