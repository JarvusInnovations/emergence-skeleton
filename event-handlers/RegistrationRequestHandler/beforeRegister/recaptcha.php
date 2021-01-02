<?php

if ($recaptcha = \RemoteSystems\ReCaptcha::getInstance()) {
    $recaptchaResponse = $recaptcha->verify($_EVENT['requestData']['g-recaptcha-response'], Emergence\Site\Client::getAddress());

    if (!$recaptchaResponse->isSuccess()) {
        $_EVENT['additionalErrors']['ReCaptcha'] = 'Please prove that you aren\'t a spam robot by completing the reCAPTCHA';
    }
}
