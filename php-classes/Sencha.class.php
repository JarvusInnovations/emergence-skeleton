<?php

class Sencha
{
    static public $frameworks = array(
    	'ext' => array(
			'defaultVersion' => '4.2.2.1144'
			,'mappedVersions' => array(
				'4.2.1' => '4.2.1.883'
				,'4.2.2' => '4.2.2.1144'
                ,'4.2' => '4.2.2.1144'
                ,'5.0.0' => '5.0.0.970'
                ,'5.0' => '5.0.0.970'
			)
		)
		,'touch' => array(
			'defaultVersion' => '2.3.1'
			,'mappedVersions' => array(
				'2.2.1' => '2.2.1.2'
			)
		)
	);
	
	static public $defaultCmdVersion = '4.0.4.84';
	
	static public $cmdPath = '/usr/local/bin/Sencha/Cmd';
	static public $binPaths = array('/bin','/usr/bin','/usr/local/bin');
	
	static public function buildCmd()
	{
		$args = func_get_args();
		if (!$cmdVersion = array_shift($args)) {
			$cmdVersion = static::$defaultCmdVersion;
		}
		
		$cmd = sprintf('SENCHA_CMD_3_0_0="%1$s" PATH="%2$s" %1$s/sencha', static::$cmdPath.'/'.$cmdVersion, implode(':', static::$binPaths));
		
		foreach ($args AS $arg) {
			if (is_string($arg)) {
				$cmd .= ' ' . $arg;
			} elseif(is_array($arg)) {
				$cmd .= ' ' . implode(' ', $arg);
			}
		}
		
		return $cmd;
	}
	
	static public function loadProperties($file)
	{
		$properties = array();
		$fp = fopen($file, 'r');
		
		while($line = fgetss($fp))
		{
			// clean out space and comments
			$line = preg_replace('/\s*([^#\n\r]*)\s*(#.*)?/', '$1', $line);
			
			if($line)
			{
				list($key, $value) = explode('=', $line, 2);
				$properties[$key] = $value;
			}
		}
		
		fclose($fp);
		
		return $properties;
	}
	
	static public function isVersionNewer($oldVersion, $newVersion)
	{
		$oldVersion = explode('.', $oldVersion);
		$newVersion = explode('.', $newVersion);
		
		while(count($oldVersion) || count($newVersion)) {
			$oldVersion[0] = (integer)$oldVersion[0];
			$newVersion[0] = (integer)$newVersion[0];
			
			if($newVersion[0] == $oldVersion[0]) {
				array_shift($oldVersion);
				array_shift($newVersion);
				continue;
			} elseif($newVersion[0] > $oldVersion[0]) {
				return true;
			}
			else {
				return false;
			}
		}
		
		return false;
	}
    
    static public function normalizeFrameworkVersion($framework, $version)
    {
    	$mappedVersions = static::$frameworks[$framework]['mappedVersions'];
		return $mappedVersions && array_key_exists($version, $mappedVersions) ? $mappedVersions[$version] : $version;
    }
    
    static public function getVersionedFrameworkPath($framework, $filePath, $version = null)
    {
        if (!$version) {
            $version = Sencha::$frameworks[$framework]['defaultVersion'];
        }
        
        $version = Sencha::normalizeFrameworkVersion($framework, $version);
        
    	if (is_string($filePath)) {
			$filePath = Site::splitPath($filePath);
		}
        
		$assetPath = Sencha_RequestHandler::$externalRoot . '/' . $framework . '-' . $version . '/' . implode('/', $filePath);
        
        array_unshift($filePath, 'sencha-workspace', "$framework-$version");
        $Asset = Site::resolvePath($filePath);
		
		if($Asset) {
			return $assetPath . '?_sha1=' . $Asset->SHA1;
		}
		else {
			return $assetPath;
		}
    }
    
    static public function getVersionedLibraryPath($filePath)
    {
        if (is_string($filePath)) {
			$filePath = Site::splitPath($filePath);
		}
        
		$assetPath = Sencha_RequestHandler::$externalRoot . '/x/' . implode('/', $filePath);
        
        array_unshift($filePath, 'ext-library');
        $Asset = Site::resolvePath($filePath);
		
		if($Asset) {
			return $assetPath . '?_sha1=' . $Asset->SHA1;
		}
		else {
			return $assetPath;
		}
    }
}