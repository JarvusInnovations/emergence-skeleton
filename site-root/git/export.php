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

// start the process
set_time_limit(0);
Benchmark::startLive();
Benchmark::mark("configured request: repoName=$repoName");


// get paths
$repoPath = "$_SERVER[SITE_ROOT]/site-data/git/$repoName";

// check if there is an existing repo
if (!is_dir("$repoPath/.git")) {
    die("$repoPath does not contain .git");
}


// get repo
chdir($repoPath);

// sync trees
foreach ($repoCfg['trees'] AS $srcPath => $treeOptions) {
    if (is_string($treeOptions)) {
        $treeOptions = array(
            'path' => $treeOptions
        );
    }

    $treeOptions = array_merge($exportOptions, $treeOptions, ['dataPath' => false]);

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

Benchmark::mark("wrote all changes");