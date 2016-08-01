<?php

namespace Emergence\Git;

use Exception;

use Site;
use SiteFile;
use Emergence_FS;

class Layer
{
    public static $layers = [];

    protected static $instances = [];

	protected $id;
	protected $config;
	protected $gitWrapper;

    public static function __classLoaded()
    {
		// copy config from legacy class if not defined locally
		if (empty(static::$layers) && class_exists('Git')) {
			static::$layers = \Git::$repositories;
		}
    }

	public function __construct($id, $config = [])
	{
		$this->id = $id;
		$this->config = $config;
	}

	public static function getById($id)
	{
		if (array_key_exists($id, static::$instances)) {
			return static::$instances[$id];
		}

		if (!array_key_exists($id, static::$layers)) {
			return null;
		}

		return static::$instances[$id] = new static($id, static::$layers[$id]);
	}

	public static function getAll()
	{
		foreach (static::$layers AS $id => $cfg) {
			if (!array_key_exists($id, static::$instances)) {
				static::$instances[$id] = new static($id, $cfg);
			}
		}

		return static::$instances;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getConfig($key = null)
	{
		return $key ? $this->config[$key] : $this->config;
	}

	public function getRepositoryPath()
	{
		return Site::$rootPath . '/site-data/git/' . $this->id;
	}

	public function isRepositoryInitialized()
	{
		return is_dir($this->getRepositoryPath());
	}

	public function isRemoteHttp()
	{
		return preg_match('#^https?://#i', $this->getConfig('remote'));
	}

	public function getGitWrapper()
	{
		if (!$this->gitWrapper) {
			$this->gitWrapper = new \Gitonomy\Git\Repository($this->getRepositoryPath(), [
                'environment_variables' => [
                    'GIT_SSH' => $this->getRepositoryPath() . '.git.sh'
		        ]
            ]);
		}

		return $this->gitWrapper;
	}

	protected static function getTreeOptions($key, $value)
	{
		if (is_string($value)) {
			$treeOptions = [
				'gitPath' => $value
			];
		} else {
			$treeOptions = $value;
		}

		if (is_string($key)) {
			$treeOptions['vfsPath'] = $key;
		}

		if (!$treeOptions['vfsPath']) {
			$treeOptions['vfsPath'] = $treeOptions['path'] ?: $treeOptions['gitPath'];
		}

		if (!$treeOptions['gitPath']) {
			$treeOptions['gitPath'] = $treeOptions['path'] ?: $treeOptions['vfsPath'];
		}

		unset($treeOptions['path']);

	    if (is_string($treeOptions['exclude'])) {
	        $treeOptions['exclude'] = array($treeOptions['exclude']);
	    }

		return $treeOptions;
	}

	public function getWorkingBranch($full = false)
	{
		$args = ['HEAD'];

		if (!$full) {
			$args[] = '--short';
		}

		return trim($this->getGitWrapper()->run('symbolic-ref', $args));
	}

	public function getUpstreamBranch()
	{
		return trim($this->getGitWrapper()->run('for-each-ref', [
			'--format=%(upstream:short)',
			$this->getWorkingBranch(true)
		]));
	}

	public function initializeRepository($privateKey = null, $publicKey = null)
	{
		// sanity checks
		if (!$remote = $this->getConfig('remote')) {
			throw new \Exception('"remote" config required');
		}

		// get paths
		$repoPath = $this->getRepositoryPath();

		if (!is_dir($repoPath)) {
			mkdir($repoPath, 0777, true);
		}

		// check if there is an existing repo
		if (is_dir("$repoPath/.git")) {
			throw new \Exception("$repoPath already contains a .git repo directory");
		}

		// write keys to file
    	if ($privateKey && ($privateKey = trim($privateKey))) {
			$privateKeyPath = "$repoPath.key";
			file_put_contents($privateKeyPath, $privateKey);
			chmod($privateKeyPath, 0600);
		}

    	if ($publicKey && ($publicKey = trim($publicKey))) {
			$publicKeyPath = "$repoPath.pub";
			file_put_contents($publicKeyPath, $publicKey);
			chmod($publicKeyPath, 0600);
		}

		// write git wrapper to file
		$gitWrapperPath = "$repoPath.git.sh";

		$gitWrapper = '#!/bin/bash' . PHP_EOL;
        $gitWrapper .= 'ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';
		if (isset($privateKeyPath)) {
			$gitWrapper .= ' -i ' . escapeshellarg($privateKeyPath);
		}
		$gitWrapper .= ' $1 $2';

		file_put_contents($gitWrapperPath, $gitWrapper);
		chmod($gitWrapperPath, 0700);

		// create new repo
		$git = \Gitonomy\Git\Admin::init($repoPath, false, [
            'environment_variables' => [
                'GIT_SSH' => $gitWrapperPath
	        ]
        ]);

		// add remote
		$git->run('remote', ['add', 'origin', $remote]);

		// fetch remote branches
		$git->run('fetch', ['--all']);

		// create local working branch
		$originBranch = $this->getConfig('originBranch') ?: 'master';
		$workingBranch = $this->getConfig('workingBranch') ?: $originBranch;
		$git->run('checkout', ['-b', $workingBranch, "origin/$originBranch"]);

		return true;
	}

	public function syncToDisk()
	{
		$results = [];
		$exportOptions = [
			'localOnly' => true
		];

		if ($this->getConfig('localOnly') === false) {
			$exportOptions['localOnly'] = false;
		}

		chdir($this->getRepositoryPath());

		foreach ($this->getConfig('trees') AS $treeKey => $treeValue) {
			$treeOptions = array_merge(
				$exportOptions,
				static::getTreeOptions($treeKey, $treeValue),
				[
					'dataPath' => false
				]
			);

			$result = [];

            try {
    			if ($srcFileNode = Site::resolvePath($treeOptions['vfsPath'])) {
    			    if ($srcFileNode instanceof SiteFile) {
    			        $destDir = dirname($treeOptions['gitPath']);

    			        if ($destDir && !is_dir($destDir)) {
    			            mkdir($destDir, 0777, true);
    			        }

    			        copy($srcFileNode->RealPath, $treeOptions['gitPath']);
    					$result = ['analyzed' => 1, 'written' => 1];
    			    } else {
    			    	$result = Emergence_FS::exportTree($treeOptions['vfsPath'], $treeOptions['gitPath'], $treeOptions);
    			    }

    				$result['success'] = true;
    			} else {
    				$result['success'] = false;
    			}
            } catch (Exception $e) {
    			$result['success'] = false;
				$result['error'] = $e->getMessage();
            }

			$results[$treeOptions['vfsPath']] = $result;
		}

		return $results;
	}

	public function syncFromDisk()
	{
		$results = [];

		chdir($this->getRepositoryPath());

		foreach ($this->getConfig('trees') AS $treeKey => $treeValue) {
			$treeOptions = array_merge(
				static::getTreeOptions($treeKey, $treeValue),
				[
					'dataPath' => false
				]
			);

			$treeOptions['exclude'][] = '#(^|/)\\.git(/|$)#';

			$result = [];

		    try {
		        if (is_file($treeOptions['gitPath'])) {
		            $sha1 = sha1_file($treeOptions['gitPath']);
		            $existingNode = Site::resolvePath($treeOptions['vfsPath']);

					$result['filesAnalyzed'] = 1;

		            if (!$existingNode || $existingNode->SHA1 != $sha1) {
		                $fileRecord = SiteFile::createFromPath($treeOptions['vfsPath'], null, $existingNode ? $existingNode->ID : null);
		        		SiteFile::saveRecordData($fileRecord, fopen($treeOptions['gitPath'], 'r'), $sha1);
						$result['filesUpdated'] = 1;
		            } else {
						$result['filesUpdated'] = 0;
		            }
		        } else {
		            $result = Emergence_FS::importTree($treeOptions['gitPath'], $treeOptions['vfsPath'], $treeOptions);
		        }

				$result['success'] = true;
		    } catch (Exception $e) {
				$result['success'] = false;
				$result['error'] = $e->getMessage();
		    }

			$results[$treeOptions['vfsPath']] = $result;
		}

		return $results;
	}
}