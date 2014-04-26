<?php

namespace Emergence\DAV;

class DevelopRequestHandler extends \RequestHandler
{
    static public function handleRequest()
    {
        if (extension_loaded('newrelic')) {
            newrelic_disable_autorum();
        }

        // retrieve authentication attempt
        $authEngine = new \Sabre\HTTP\BasicAuth();
        $authEngine->setRealm('Develop '.\Site::$title);
        $authUserPass = $authEngine->getUserPass();

        // try to get user
        $userClass = \User::$defaultClass;
        $User = $userClass::getByLogin($authUserPass[0], $authUserPass[1]);

        // send auth request if login is inadiquate
        if (!$User || !$User->hasAccountLevel('Developer')) {
            $authEngine->requireLogin();
            die("Authentication required\n");
        }

        // store login to session
        if (isset($GLOBALS['Session'])) {
            $GLOBALS['Session'] = $GLOBALS['Session']->changeClass('UserSession', array(
                'PersonID' => $User->ID
            ));
        }

        // handle /develop request
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && !static::peekPath()) {
            \RequestHandler::respond('app/ext', array(
                'App' => \Sencha_App::getByName('EmergenceEditor')
                ,'mode' => 'production'
                ,'title' => 'EmergenceEditor'
            ));
        }

        // switch to JSON response mode
        if (static::peekPath() == 'json') {
            static::$responseMode = static::shiftPath();
        }

        // detect base path
        $basePath = array_slice(\Site::$requestPath, 0, count(\Site::$resolvedPath));
        if (static::$responseMode == 'json') {
            $basePath[] = 'json';
        }

        // initial and configure SabreDAV
        $server = new \Sabre\DAV\Server(new RootCollection());
        $server->setBaseUri('/' . join('/', $basePath));

        // The lock manager is reponsible for making sure users don't overwrite each others changes. Change 'data' to a different
        // directory, if you're storing your data somewhere else.
#       $lockBackend = new Sabre_DAV_Locks_Backend_FS('/tmp/dav-lock');
#       $lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
#       $server->addPlugin($lockPlugin);

        // filter temporary files
        $server->addPlugin(new \Sabre\DAV\TemporaryFileFilterPlugin('/tmp/dav-tmp'));

        // ?mount support
        $server->addPlugin(new \Sabre\DAV\Mount\Plugin());

        // emergence :)
        $server->addPlugin(new \Emergence\DAV\ServerPlugin());

        // All we need to do now, is to fire up the server
        $server->exec();
    }
}