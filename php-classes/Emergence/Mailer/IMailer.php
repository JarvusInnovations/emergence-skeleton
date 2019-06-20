<?php

namespace Emergence\Mailer;

interface IMailer
{
    public static function send($to, $subject, $body, $from = false);
    public static function sendFromTemplate($to, $template, $data = [], $options = []);
    public static function renderTemplate($template, $data = []);
}
