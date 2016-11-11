<?php

    namespace Emergence\Git;

use Exception;

use Site;
use SiteFile;
use Emergence_FS;

class Repo
{
	public static $layers = [];

	protected static $instances = [];

	protected $ID;
	protected $config;
	protected $gitWrapper;
	protected $_isFetched;

	public static function __classLoaded()
	{
		// copy config from legacy class if not defined locally
		if (empty(static::$layers) && class_exists('Git')) {
			static::$layers = \Git::$repositories;
		}
	}

	public function __construct($ID, $config = [])
	{
		$this->ID = $ID;
		$this->config = $config;
	}

	public static function getById($ID)
	{
		if (array_key_exists($ID, static::$instances)) {
			return static::$instances[$ID];
		}

		if (!array_key_exists($ID, static::$layers)) {
			return null;
		}

		return static::$instances[$ID] = new static($ID, static::$layers[$ID]);
	}

	public function __get($name)
	{
		return $this->getValue($name);
	}

	public function getValue($name)
	{
		switch ($name) {
			case 'ID':
				return $this->getId();

			case 'Config':
				return $this->getConfig();

			case 'Initialized':
				return $this->isRepositoryInitialized();

			case 'Data':
				return $this->getData();

			case 'Remote':
				return $this->getConfig('remote');

			case 'isFetched':
				return $this->_isFetched;

			case 'PrivateKeyPath':
				return $this->getKeyPath();

		}

		if($this->isRepositoryInitialized()) {
			$git = $this->getGitWrapper();

			if(!$this->_isFetched) {
				$git->run('fetch', ['--all']);
				$this->_isFetched = true;
			}


			switch ($name) {
				case 'Status':
					return $git->run('status', ['-sb']);

				case 'AdvancedStatus':
					return $this->getAdvancedStatus();

				case 'WorkingBranch':
					return $this->getWorkingBranch();

				case 'UpstreamBranch':
					return $this->getUpstreamBranch();
			}
		}
	}

	public function getData()
	{
		$data = [
			'ID' => $this->ID,
			'Config' => $this->config,
			'Initialized'  => $this->initialized
		];

		if ($data['initialized']) {
			$data = array_merge($data, [
				'Status' => $this->status,
				'AdvancedStatus' => $this->AdvancedStatus,
				'WorkingBranch' => $this->WorkingBranch,
				'UpstreamBranch' => $this->getUpstreamBranch
			]);
		}

		return $data;
	}

	public function getAdvancedStatus()
	{
		$git = $this->getGitWrapper();


		$rawStatus = $git->run('status', ['-suall']);


		$lines = preg_split("/[\r\n]+/", $rawStatus);

		$data = [];

		foreach($lines as $line) {
			$matches = [];
			preg_match("/^(.{2}) (.+) -> (.+)/",$line,$matches);

			if(!count($matches)) {
				preg_match("/^(.{2}) (.+)/",$line,$matches);
			}

			$parsedLine = [
				'Raw' => $matches[0],
				'Code' => $matches[1],
				'File' => $matches[3]?$matches[3]:$matches[2]
			];

			if($matches[3]) {
				$parsedLine['OriginalFile'] = $matches[2];
			}


			// Code
			/*
                ' ' = unmodified

                M = modified

                A = added

                D = deleted

                R = renamed

                C = copied

                U = updated but unmerged

                Ignored files are not listed, unless --ignored option is in effect, in which case XY are !!.

                X          Y     Meaning
                -------------------------------------------------
                          [MD]   not updated
                M        [ MD]   updated in index
                A        [ MD]   added to index
                D         [ M]   deleted from index
                R        [ MD]   renamed in index
                C        [ MD]   copied in index
                [MARC]           index and work tree matches
                [ MARC]     M    work tree changed since index
                [ MARC]     D    deleted in work tree
                -------------------------------------------------
                D           D    unmerged, both deleted
                A           U    unmerged, added by us
                U           D    unmerged, deleted by them
                U           A    unmerged, added by them
                D           U    unmerged, deleted by us
                A           A    unmerged, both added
                U           U    unmerged, both modified
                -------------------------------------------------
                ?           ?    untracked
                !           !    ignored
                -------------------------------------------------
            */
			if($parsedLine['Code'] == '??') {
				$parsedLine['Tracked'] = false;
			}
			else if($parsedLine['Code'] == '!!') {
				$parsedLine['Ignored'] = true;
			}
			else {
				$parsedLine['Tracked'] = true;
			}

			if($parsedLine['Code'][0] != ' ' && $parsedLine['Code'][0] != '?') {
				$parsedLine['Staged'] = true;
			} else {
				$parsedLine['Staged'] = false;
			}

			if($parsedLine['File']) {
				$data['Everything'][] = $parsedLine;
				if($parsedLine['Staged']) {
					$data['Staged'][] = $parsedLine;
				}
				else {
					$data['Unstaged'][] = $parsedLine;
				}
				if($parsedLine['Tracked']) {
					$data['Tracked'][] = $parsedLine;
				}
				else {
					$data['Untracked'][] = $parsedLine;   
				}
			}
		}

		return $data;
	}

	public static function getAll()
	{
		foreach (static::$layers AS $ID => $cfg) {
			if (!array_key_exists($ID, static::$instances)) {
				static::$instances[$ID] = new static($ID, $cfg);
			}
		}

		return static::$instances;
	}

	public function getId()
	{
		return $this->ID;
	}

	public function getConfig($key = null)
	{
		return $key ? $this->config[$key] : $this->config;
	}

	public function getRepositoryPath()
	{
		return Site::$rootPath . '/site-data/git/' . $this->ID;
	}

	public function getPrivateKeyPath()
	{
		return $this->getRepositoryPath().'.key';
	}

	public function getPublicKeyPath()
	{
		return $this->getRepositoryPath().'.pub';
	}

	public function privateKeyExists()
	{
		return file_exists($this->getPrivateKeyPath());
	}

	public function getPublicFingerprint()
	{
		$output = exec($command = 'ssh-keygen -lf '.$this->getPublicKeyPath());

		$parts = preg_split('/\s+/', $output);

		return $parts;
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

	public function setKeys($privateKey,$publicKey)
	{
		if ($privateKey && ($privateKey = trim($privateKey))) {
			$privateKeyPath = $this->getPrivateKeyPath();
			file_put_contents($privateKeyPath, $privateKey);
			chmod($privateKeyPath, 0600);
		}
		if ($publicKey && ($publicKey = trim($publicKey))) {
			$publicKeyPath =  $this->getPublicKeyPath();
			file_put_contents($publicKeyPath, $publicKey);
			chmod($publicKeyPath, 0600);
		}
	}

	public function initializeRepository()
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

		// write git wrapper to file
		$gitWrapperPath = "$repoPath.git.sh";

		$gitWrapper = '#!/bin/bash' . PHP_EOL;
		$gitWrapper .= 'ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no';
		$gitWrapper .= ' -i '.$this->getPrivateKeyPath();
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