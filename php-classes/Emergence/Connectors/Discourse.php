<?php

namespace Emergence\Connectors;

use Site;
use Emergence\People\IPerson;

class Discourse extends \Emergence\Connectors\AbstractConnector implements \Emergence\Connectors\IIdentityConsumer
{
    use \Emergence\Connectors\IdentityConsumerTrait;
    
    public static $host;
    public static $ssoSecret;

    public static $title = 'Discourse';
    public static $connectorId = 'discourse';
    
    public static function handleLoginRequest(IPerson $Person)
    {
        if (empty($_GET['sso'])) {
            return static::throwInvalidRequestError('sso parameter missing');
        }

        if (empty($_GET['sig'])) {
            return static::throwInvalidRequestError('sig parameter missing');
        }
        
        if ($_GET['sig'] != hash_hmac('sha256', $_GET['sso'], static::$ssoSecret)) {
            return static::throwInvalidRequestError('sig is invalid');
        }
        
        if (!static::$host || !static::$ssoSecret) {
            return static::throwError('Discourse SSO is not fully configured yet');
        }


        // decode payload into associative array
        $payload = base64_decode($_GET['sso']);
        parse_str($payload, $payload);


        // append return values to payload
        $payload['name'] = $Person->FullName;
        $payload['email'] = $Person->Email;
        $payload['username'] = $Person->Username;
        $payload['external_id'] = $Person->ID;
        $payload['about_me'] = $Person->About;
        
        if ($Person->PrimaryPhoto) {
            $payload['avatar_url'] = 'http://' . Site::getConfig('primary_hostname') . $Person->PrimaryPhoto->WebPath;
        }


        // re-encode payload
        $payload = base64_encode(http_build_query($payload));


        // redirect with payload and its signature
        Site::redirect('http://' . static::$host . '/session/sso_login', [
            'sso' => $payload,
            'sig' => hash_hmac('sha256', $payload, static::$ssoSecret)
        ]);
    }
}