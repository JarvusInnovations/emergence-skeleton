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


// get key
if(empty($_POST['privateKey'])) {
	die(
		'<form method="POST">'
			.'<label>Deploy private key:<br>'
				.'<textarea name="privateKey" rows="30" cols="65">'
				."-----BEGIN RSA PRIVATE KEY-----\n-----END RSA PRIVATE KEY-----"
				.'</textarea>'
			.'</label>'
			.'<p><input type="submit" value="Create repo"></p>'
		.'</form>'
	);
}


// start the process
set_time_limit(0);
Benchmark::startLive();
Benchmark::mark("configured request: repoName=$repoName");


// get paths
$repoPath = "$_SERVER[SITE_ROOT]/site-data/git/$repoName";
$keyPath = "$repoPath.key";
$gitWrapperPath = "$repoPath.git.sh";

if(!is_dir($repoPath)) {
	mkdir($repoPath, 0777, true);
	Benchmark::mark("created directory: $repoPath");
}


// check if there is an existing repo
if(is_dir("$repoPath/.git")) {
	die("$repoPath already contains a .git repo directory");
}


// write key to file
file_put_contents($keyPath, $_POST['privateKey']);
chmod($keyPath, 0600);
Benchmark::mark("saved key to $keyPath");


// write git wrapper to file
file_put_contents($gitWrapperPath, "#!/bin/bash\nssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i $keyPath \$1 \$2");
chmod($gitWrapperPath, 0700);
putenv("GIT_SSH=$gitWrapperPath");
Benchmark::mark("saved git wrapper to $gitWrapperPath");



// create new repo
$repo = PHPGit_Repository::create($repoPath, !empty($_REQUEST['debug']));
Benchmark::mark("initialized git repo in $repoPath");


// add remote
$repo->git("remote add origin $repoCfg[remote]");
Benchmark::mark("added origin $repoCfg[remote]");


// fetch remote branch
if(!empty($repoCfg['originBranch'])) {
	$repo->git("fetch origin $repoCfg[originBranch]");
	Benchmark::mark("fetched origin/$repoCfg[originBranch]");
}

// create local working branch
if(!empty($repoCfg['originBranch'])) {
	$repo->git("checkout -b $repoCfg[workingBranch] FETCH_HEAD");
	Benchmark::mark("created local branch $repoCfg[workingBranch]");
}
else {
	die('TODO: handle initializing repo without originBranch'); // see http://git.661346.n2.nabble.com/how-to-start-with-non-master-branch-td3284326.html
}