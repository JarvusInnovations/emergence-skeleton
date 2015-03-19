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
$keyPath = "$repoPath.key";
$gitWrapperPath = "$repoPath.git.sh";
putenv("GIT_SSH=$gitWrapperPath");


// check if there is an existing repo
if(!is_dir("$repoPath/.git")) {
	die("$repoPath does not contain .git");
}


// get repo
chdir($repoPath);
$repo = new PHPGit_Repository($repoPath, !empty($_REQUEST['debug']));
Benchmark::mark("loaded git repo in $repoPath");


// verify repo state
if($repo->getCurrentBranch() != $repoCfg['workingBranch']) {
	die("Current branch in $repoPath is not $repoCfg[workingBranch]; aborting.");
}
Benchmark::mark("verified working branch");


// pull changes
$repo->git("pull origin $repoCfg[workingBranch]");
Benchmark::mark("pulled from origin/$repoCfg[workingBranch]");


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
	
	$treeOptions['exclude'][] = '#(^|/)\\.git(/|$)#';
    
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
        $cachedFiles = Emergence_FS::cacheTree($srcPath);
        Benchmark::mark("precached $srcPath: ".$cachedFiles);

        $exportResult = Emergence_FS::importTree($treeOptions['path'], $srcPath, $treeOptions);
    	Benchmark::mark("importing directory $srcPath from $treeOptions[path]: ".http_build_query($exportResult));
    }
}


// commit changes
#$repo->git('add --all');
#
#$repo->git(sprintf(
#	'commit -n -m "%s" --author="%s <%s>"'
#	,addslashes($_POST['message'])
#	,$GLOBALS['Session']->Person->FullName
#	,$GLOBALS['Session']->Person->Email
#));
#Benchmark::mark("committed all changes");
