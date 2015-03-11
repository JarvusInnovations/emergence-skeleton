<?php

namespace Emergence\Mailer;

abstract class AbstractMailer implements IMailer
{
    static public function getDefaultFrom()
    {
		return Mailer::$defaultFrom ? Mailer::$defaultFrom : \Site::getConfig('label') . ' <support@'.\Site::getConfig('primary_hostname').'>';
	}
	
	static public function sendFromTemplate($to, $template, $data = array(), $options = array())
	{
		$email = static::renderTemplate($template, $data);
		
		return static::send($to, $email['subject'], $email['body'], $email['from'], $options);
	}
	
	static public function renderTemplate($template, $data = array())
	{
        $email = array(
            'from' => null,
            'subject' => null,
            'body' => trim(\Emergence\Dwoo\Engine::getSource($template.'.email', $data))
        );

		$templateVars = \Emergence\Dwoo\Engine::getInstance()->scope;
        
        if (isset($templateVars['from'])) {
            $email['from'] = trim(preg_replace('/\s+/', ' ', $templateVars['from']));
        }

        if (isset($templateVars['subject'])) {
            $email['subject'] = trim(preg_replace('/\s+/', ' ', $templateVars['subject']));
        }

		return $email;
	}
}