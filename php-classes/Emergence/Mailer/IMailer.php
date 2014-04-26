<?php

namespace Emergence\Mailer;

interface IMailer
{
    static public function send($to, $subject, $body, $from = false);
    static public function sendFromTemplate($to, $template, $data = array(), $options = array());
	static public function renderTemplate($template, $data = array());
}