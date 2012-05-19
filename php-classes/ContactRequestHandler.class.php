<?php



 class ContactRequestHandler extends RequestHandler
{

	static public $emailTo;
	static public $emailSubject = 'Contact form submission';
	static public $emailFrom = false;
	static public $validators = array();
	static public $formatters = array();
	static public $excludeFields = array('path');

	static public function handleRequest()
	{
	
		// handle JSON requests
		if(static::peekPath() == 'json')
		{
			static::$responseMode = static::shiftPath();
		}
		
		// route request
		return static::handleContactRequest();
	}
	
	
	static public function handleContactRequest()
	{
		// get optional subform name
		$subform = static::shiftPath();
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// validate
			$Validator = new RecordValidator($_REQUEST);
			
			foreach(static::$validators AS $validatorConfig)
			{
				// execute callable validator
				if(is_callable($validatorConfig))
				{
					$validatorConfig($Validator, $subform);
				}
				else
				{
					if(
						!empty($validatorConfig['subforms'])
						&& is_array($validatorConfig['subforms'])
						&& (!$subform || !in_array($subform, $validatorConfig['subforms']))
					)
					{
						// skip if validator specific to other subforms
						continue;
					}
					
					$Validator->validate($validatorConfig);
				}
			}
		
			if(!$Validator->hasErrors())
			{
				// save to database
				$Submission = new ContactSubmission::$defaultClass();
				$Submission->Data = array_diff_key($_REQUEST, array_flip(static::$excludeFields));
				$Submission->Subform = $subform;
				$Submission->save();
				
				// generate email report
				if(!empty(static::$emailTo))
				{
					$subject = static::$emailSubject;
					if(!empty($_REQUEST['Subject']))
					{
						$subject .= ': '.$_REQUEST['Subject'];
					}
					
					$html = TemplateResponse::getSource('staffNotice.email', array(
						'Submission' => $Submission
						,'formatters' => static::$formatters
					));
					
				/*
					$html = '<table border="0">';
					foreach($_REQUEST AS $field=>$value)
					{
						if(in_array($field, static::$excludeFields))
						{
							continue;
						}
						
						if(is_array($value))
						{
							$value = implode(', ', $value);
						}
						
						$html .= sprintf(
							'<tr><th scope="row" valign="top" align="right">%s</th><td>%s</td></tr>'
							, htmlspecialchars($field)
							, nl2br(htmlspecialchars($value))
						);
					}
					$html .= '</table>';
					*/
					
					// set optional headers
					$headers = '';
					if(!empty($_REQUEST['Email']) && Validators::email($_REQUEST['Email']))
					{
						$headers .= "Reply-To: $_REQUEST[Email]" . PHP_EOL;
					}
					
					// send email
					Email::send(static::$emailTo, $subject, $html, static::$emailFrom, $headers);
				}
				
				// respond success
				return static::respond('contactSubmitted', array(
					'success' => true
					,'subform' => $subform
				));
			}
		}	
	
	
		return static::respond('contact', array(
			'validationErrors' => isset($Validator) ? $Validator->getErrors() : array()
			,'subform' => $subform
		));
	}

}