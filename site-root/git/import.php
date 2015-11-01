<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
    
    
// get repo
if(empty($_REQUEST['repo'])) {
    die('Parameter "repo" required');
}

$repoName = $_REQUEST['repo'];

if(!array_key_exists($repoName, Git::$repositories)) {
    die("Repo '$repoName' is not defined in Git::\$repositories");
}

$repoCfg = Git::$repositories[$repoName];


// start the process
set_time_limit(0);
Benchmark::startLive();
Benchmark::mark("configured request: repoName=$repoName");


// get paths
$repoPath = "$_SERVER[SITE_ROOT]/site-data/git/$repoName";

// check if there is an existing repo
if(!is_dir("$repoPath/.git")) {
	die("$repoPath does not contain .git");
}

// get repo
chdir($repoPath);

// sync trees
foreach($repoCfg['trees'] AS $srcPath => $treeOptions) {
	
	if(is_string($treeOptions)) {
		$treeOptions = array(
			'path' => $treeOptions
		);
	}

	if(!is_string($srcPath)) {
		$srcPath = $treeOptions['path'];
	}
	elseif(!$treeOptions['path']) {
		$treeOptions['path'] = $srcPath;
	}

    if (is_string($treeOptions['exclude'])) {
        $treeOptions['exclude'] = array($treeOptions['exclude']);
    }

	$treeOptions['exclude'][] = '#(^|/)\\.git(/|$)#';
    $treeOptions['dataPath'] = false;
    
    try {
        if (is_file($treeOptions['path'])) {
            $sha1 = sha1_file($treeOptions['path']);
            $existingNode = Site::resolvePath($srcPath);
            
            if (!$existingNode || $existingNode->SHA1 != $sha1) {
                $fileRecord = SiteFile::createFromPath($srcPath, null, $existingNode ? $existingNode->ID : null);
        		SiteFile::saveRecordData($fileRecord, fopen($treeOptions['path'], 'r'), $sha1);
                Benchmark::mark("importing file $srcPath from $treeOptions[path]");
            } else {
                Benchmark::mark("skipped unchanged file $srcPath from $treeOptions[path]");
            }
        } else {
            $exportResult = Emergence_FS::importTree($treeOptions['path'], $srcPath, $treeOptions);
        	Benchmark::mark("importing directory $srcPath from $treeOptions[path]: ".http_build_query($exportResult));
        }
    } catch (Exception $e) {
        Benchmark::mark("failed to import directory $srcPath from $treeOptions[path]: ".$e->getMessage());
    }
}