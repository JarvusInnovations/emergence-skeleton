<?php

namespace Emergence\Git;

use Site;
use Exception;

class RequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json'
	];

	protected static function getRequestedLayer()
	{
		// get repo
		if (empty($_REQUEST['layer'])) {
		    throw new Exception('Parameter "layer" required');
		}

		$layerId = $_REQUEST['layer'];

		if(!$layer = Layer::getById($layerId)) {
			throw new Exception('Requested layer "' . $layerId . '" is not defined');
		}

		return $layer;
	}

    public static function handleRequest()
    {
        return static::throwNotFoundError();
    }

	public static function handleStatusRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		$layers = [];

		foreach (Layer::getAll() AS $layer) {
			$layerData = [
				'id' => $layer->getId(),
				'config' => $layer->getConfig(),
				'initialized'  => $layer->isRepositoryInitialized()
			];

			if ($layerData['initialized']) {
				$git = $layer->getGitWrapper();

				// fetch every branch
				$git->run('fetch', ['--all']);

				$layerData['status'] = $git->run('status', ['-sb']); // force color ansi with -c color.status=always
				$layerData['workingBranch'] = $layer->getWorkingBranch();
				$layerData['upstreamBranch'] = $layer->getUpstreamBranch();
			}

			$layers[] = $layerData;
		}

		return static::respond('layers', [
			'layers' => $layers
		]);
	}

	public static function handleInitRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		try {
			$layer = static::getRequestedLayer();
		} catch (Exception $e) {
			return static::throwInvalidRequestError($e->getMessage());
		}

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $keyTmpPath = tempnam(sys_get_temp_dir(), 'git-key');
            unlink($keyTmpPath);
            $keyStatus = exec($command = "ssh-keygen -q -t rsa -N '' -C '".Site::getConfig('primary_hostname')."' -f '$keyTmpPath'; echo $?");

            if ($keyStatus != '0') {
                throw new Exception("Failed to execute command: $command\nExit output: $keyStatus");
            }

            $publicKey = file_get_contents("$keyTmpPath.pub");
            $privateKey = file_get_contents($keyTmpPath);

            unlink("$keyTmpPath.pub");
            unlink($keyTmpPath);

			return static::respond('configureInit', [
				'layer' => $layer,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey
			]);
		}

		return static::respond('repositoryInitialized', [
			'layer' => $layer,
			'results' => $layer->initializeRepository($_POST['privateKey'], $_POST['publicKey'])
		]);
	}

	public static function handleSyncToDiskRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return static::throwInvalidRequestError('Request must be POST');
		}

        // Disable diagnostics by default for this operation as a high volume of queries may be needed
        Site::$debug = !empty($_GET['debug']);

		try {
			$layer = static::getRequestedLayer();
		} catch (Exception $e) {
			return static::throwInvalidRequestError($e->getMessage());
		}

		return static::respond('syncedToDisk', [
			'results' => $layer->syncToDisk()
		]);
	}

	public static function handleSyncFromDiskRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return static::throwInvalidRequestError('Request must be POST');
		}

        // Disable diagnostics by default for this operation as a high volume of queries may be needed
        Site::$debug = !empty($_GET['debug']);

		try {
			$layer = static::getRequestedLayer();
		} catch (Exception $e) {
			return static::throwInvalidRequestError($e->getMessage());
		}

		return static::respond('syncedFromDisk', [
			'results' => $layer->syncFromDisk()
		]);
	}

	public static function handlePullRequest()
	{
		$GLOBALS['Session']->requireAccountLevel('Developer');

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return static::throwInvalidRequestError('Request must be POST');
		}

		try {
			$layer = static::getRequestedLayer();
		} catch (Exception $e) {
			return static::throwInvalidRequestError($e->getMessage());
		}

		return static::handleExecuteCommandRequest($layer, 'merge', ['--ff-only', '@{upstream}']);
	}

	public static function handleExecuteCommandRequest(Layer $layer, $command, $args = [])
	{
		return static::respond('commandExecuted', [
			'layer' => $layer,
			'command' => $command,
			'args' => $args,
			'output' => $layer->getGitWrapper()->run($command, $args)
		]);
	}
}