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


// push changes
$repo->git("push origin $repoCfg[workingBranch]");
Benchmark::mark("pushed to $repoCfg[workingBranch]");