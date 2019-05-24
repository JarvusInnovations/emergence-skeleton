<?php

namespace Emergence\Mailer;

class MailgunMailer extends AbstractMailer
{
    public static $apiKey = '';
    public static $domain = '';

    public static function send($to, $subject, $body, $from = false, $options = array())
    {
        if (!$from) {
            $from = static::getDefaultFrom();
        }

#        Full options can be found here: https://documentation.mailgun.com/en/latest/api-sending.html#sending

        return static::apiPost(array_merge($options, array(
            'to' => $to
            ,'from' => $from
            ,'subject' => $subject
            ,'html' => $body
        )));
    }

    protected static function apiPost($data)
    {
        $ch = curl_init('https://api.mailgun.net/v3/'.static::$domain.'/messages');
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . static::$apiKey);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpStatus == 200) {
            return json_decode($result, true);
        } else {
            \Emergence\Logger::general_error('MaligunMailer Delivery Error', [
                'exceptionClass' => MailgunMailer::class,
                'exceptionMessage' => $result,
                'exceptionCode' => $httpStatus
            ]);

            return false;
        }
    }
}
