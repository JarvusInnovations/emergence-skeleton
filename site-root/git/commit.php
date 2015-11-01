<?php

$GLOBALS['Session']->requireAccountLevel('Developer');


// get repo
if (empty($_REQUEST['repo'])) {
    die('Parameter "repo" required');
}

$repoName = $_REQUEST['repo'];

if (!array_key_exists($repoName, Git::$repositories)) {
    die("Repo '$repoName' is not defined in Git::\$repositories");
}

$repoCfg = Git::$repositories[$repoName];

$exportOptions = array(
    'localOnly' => false
);

if (!empty($repoCfg['localOnly'])) {
    $exportOptions['localOnly'] = true;
}

// get message
if (empty($_POST['message'])) {
    die(
        '<form method="POST">'
            .'<label>Commit message: <input type="text" name="message" size="50"></label>'
            .'<input type="submit" value="Commit">'
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
putenv("GIT_SSH=$gitWrapperPath");


// check if there is an existing repo
if (!is_dir("$repoPath/.git")) {
    die("$repoPath does not contain .git");
}


// get repo
chdir($repoPath);
$repo = new PHPGit_Repository($repoPath, !empty($_REQUEST['debug']));
Benchmark::mark("loaded git repo in $repoPath");


// verify repo state
if ($repo->getCurrentBranch() != $repoCfg['workingBranch']) {
    die("Current branch in $repoPath is not $repoCfg[workingBranch]; aborting.");
}
Benchmark::mark("verified working branch");

// sync trees
foreach ($repoCfg['trees'] AS $srcPath => $treeOptions) {
    if (is_string($treeOptions)) {
        $treeOptions = array(
            'path' => $treeOptions
        );
    }

    $treeOptions = array_merge($exportOptions, $treeOptions);

    if (!is_string($srcPath)) {
        $srcPath = $treeOptions['path'];
    } elseif (!$treeOptions['path']) {
        $treeOptions['path'] = $srcPath;
    }

    $srcFileNode = Site::resolvePath($srcPath);

    if (is_a($srcFileNode, 'SiteFile')) {
        $destDir = dirname($treeOptions['path']);

        if ($destDir && !is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        copy($srcFileNode->RealPath, $treeOptions['path']);
        Benchmark::mark("exported file $srcPath to $treeOptions[path]");
    } else {
        $exportResult = Emergence_FS::exportTree($srcPath, $treeOptions['path'], $treeOptions);
        Benchmark::mark("exported directory $srcPath to $treeOptions[path]: ".http_build_query($exportResult));
    }
}


if (!empty($_REQUEST['syncOnly'])) {
    exit();
}

// set author
$repo->git(sprintf('git config user.name "%s"', $GLOBALS['Session']->Person->FullName));
$repo->git(sprintf('git config user.email "%s"', $GLOBALS['Session']->Person->Email));

// commit changes
$repo->git('add --all');

$repo->git(sprintf(
    'commit -n -m "%s"'
    ,addslashes($_POST['message'])
));
Benchmark::mark("committed all changes");


// push changes
$repo->git("push origin $repoCfg[workingBranch]");
Benchmark::mark("pushed to $repoCfg[workingBranch]");