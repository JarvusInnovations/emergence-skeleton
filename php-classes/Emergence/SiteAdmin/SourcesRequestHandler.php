<?php

namespace Emergence\SiteAdmin;

use Emergence\Git\Source;
use Emergence\SSH\KeyPair;


class SourcesRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json'
    ];

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        if ($sourceId = static::shiftPath()) {
            if (substr($sourceId, -4) == '.git') {
                if (!$source = Source::getById(substr($sourceId, 0, -4))) {
                    return static::throwNotFoundError('source not found');
                }

                return static::handleSourceGitRequest($source);
            }

            if (!$source = Source::getById($sourceId)) {
                return static::throwNotFoundError('source not found');
            }

            return static::handleSourceRequest(Source::getById($sourceId));
        }

        return static::respond('sources', [
            'sources' => Source::getAll()
        ]);
    }

    public static function handleSourceGitRequest(Source $source)
    {
        \Debug::dumpVar($source, false, 'handleSourceGitRequest');
    }

    public static function handleSourceRequest(Source $source)
    {
        switch ($action = static::shiftPath()) {
            case 'initialize':
                return static::handleInitializeRequest($source);
            case 'deploy-key':
                return static::handleDeployKeyRequest($source);
        }

        return static::respond('source', [
            'source' => $source
        ]);
    }

    public static function handleInitializeRequest(Source $source)
    {
        $deployKey = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!empty($_POST['privateKey']) || !empty($_POST['publicKey'])) {
                if (empty($_POST['privateKey']) || empty($_POST['publicKey'])) {
                    return static::throwInvalidRequestError('Both public and private keys must be provided');
                }

                $deployKey = new KeyPair($_POST['privateKey'], $_POST['publicKey']);
                $source->setDeployKey($deployKey);
            }

            try {
                $source->initialize();
            } catch (\Exception $e) {
                return static::throwError('Failed to initialize repository: ' . $e->getMessage());
            }

            return static::respond('initialized', [
                'source' => $source
            ]);
        }

        return static::respond('initialize', [
            'source' => $source,
            'deployKey' => $source->getRemoteProtocol() == 'ssh' ? KeyPair::generate() : null
        ]);
    }

    public static function handleDeployKeyRequest(Source $source)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['privateKey']) || empty($_POST['publicKey'])) {
                return static::throwInvalidRequestError('Both public and private keys must be provided');
            }

            $deployKey = new KeyPair($_POST['privateKey'], $_POST['publicKey']);
            $source->setDeployKey($deployKey);

            return static::respond('message', [
                'message' => 'Deploy key saved',
                'returnURL' => '/site-admin/sources/' . $source->getId(),
                'returnLabel' => 'Return to ' . $source->getId()
            ]);
        }

        if (!empty($_GET['source']) && $_GET['source'] == 'generated') {
            $deployKey = KeyPair::generate();
        } else {
            $deployKey = $source->getDeployKey();
        }

        return static::respond('deployKey', [
            'source' => $source,
            'deployKey' => $deployKey
        ]);
    }
}