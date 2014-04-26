<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);

if(empty($_GET['dumpWorkspace'])) {
    Benchmark::startLive();
}

// load build cfg
$buildConfig = Sencha::loadProperties(Site::resolvePath('sencha-workspace/pages/.sencha/workspace/sencha.cfg')->RealPath);
$framework = $buildConfig['pages.framework'];
$frameworkVersion = Sencha::normalizeFrameworkVersion($framework, $buildConfig['pages.framework.version']);

if(!framework) {
    die("app.framework not found in sencha.cfg");
}

// set paths
$pagesPath = 'sencha-workspace/pages';
$frameworkPath = "sencha-workspace/$framework-$frameworkVersion";

// get temporary directory and set paths
$tmpPath = Emergence_FS::getTmpDir();
$frameworkTmpPath = "$tmpPath/$framework";
$buildTmpPath = "$tmpPath/build";

Benchmark::mark("created tmp: $tmpPath");


// precache and write pages
$cachedFiles = Emergence_FS::cacheTree($pagesPath);
Benchmark::mark("precached $cachedFiles files in $pagesPath");
$exportResult = Emergence_FS::exportTree($pagesPath, $tmpPath);
Benchmark::mark("exported $pagesPath to $tmpPath: ".http_build_query($exportResult));


// ... framework
if (!empty($_REQUEST['pullFramework'])) {
    $cachedFiles = Emergence_FS::cacheTree($frameworkPath);
    Benchmark::mark("precached $cachedFiles files in $frameworkPath");
}
$exportResult = Emergence_FS::exportTree($frameworkPath, $frameworkTmpPath);
Benchmark::mark("exported $frameworkPath to $frameworkTmpPath: ".http_build_query($exportResult));


// write any libraries from classpath
if (!empty($buildConfig['pages.classpath'])) {
    $classPaths = explode(',', $buildConfig['pages.classpath']);
    
	foreach($classPaths AS $classPath) {
		if(strpos($classPath, 'x/') === 0) {
        	$extensionPath = substr($classPath, 2);
    		$classPathSource = "ext-library/$extensionPath";
    		$classPathDest = "$tmpPath/x/$extensionPath";
    		Benchmark::mark("importing classPathSource: $classPathSource");
        	
    #		$cachedFiles = Emergence_FS::cacheTree($classPathSource);
    #		Benchmark::mark("precached $cachedFiles files in $classPathSource");
    		
            $sourceNode = Site::resolvePath($classPathSource);
            
            if (is_a($sourceNode, SiteFile)) {
                mkdir(dirname($classPathDest), 0777, true);
                copy($sourceNode->RealPath, $classPathDest);
        		Benchmark::mark("copied file $classPathSource to $classPathDest");
            } else {
            	$exportResult = Emergence_FS::exportTree($classPathSource, $classPathDest);
        		Benchmark::mark("exported $classPathSource to $classPathDest: ".http_build_query($exportResult));
            }
		}
	}
}


// change into tmpPath
chdir($tmpPath);
Benchmark::mark("chdir to: $tmpPath");


// build command
$pageNames = array();
$pageLoadCommands = array();
$pageBuildCommands = array();

foreach (glob('./src/page/*.js') AS $page) {
	$pageNames[] = $pageName = basename($page, '.js');
    
	$pageLoadCommands[] = "union -r -c Site.page.$pageName and save $pageName";
	
	if ($page != 'common.html') {
		$pageBuildCommands[] = "restore $pageName and exclude --set common and concat --yui $buildTmpPath/$pageName.js";
	}
}


// prepare cmd
$cmd = Sencha::buildCmd(
	null
    ,"-sdk $frameworkTmpPath"
	,'compile'
    ," -classpath=./src,./x"
    ,'union -r -c Site.Common and save common'
	,'and ' . join(' and ', $pageLoadCommands)
    ,count($pageNames) > 1 ? 'and intersect --min-match 2 --sets '.join(',', $pageNames) : ''
	,'and include --set common'
	,'and save common'
	,"and concat --yui $buildTmpPath/common.js"
	,count($pageBuildCommands) ? 'and ' . join(' and ', $pageBuildCommands) : ''
);
Benchmark::mark("running CMD: $cmd");

// optionally dump workspace and exit
if(!empty($_GET['dumpWorkspace']) && $_GET['dumpWorkspace'] != 'afterBuild') {
	header('Content-Type: application/x-bzip-compressed-tar');
	header('Content-Disposition: attachment; filename="'.$appName.'.'.date('Y-m-d').'.tbz"');
	chdir($tmpPath);
	passthru("tar -cjf - ./");
	exec("rm -R $tmpPath");
	exit();
}

// execute CMD
//  - optionally dump workspace and exit
if(!empty($_GET['dumpWorkspace']) && $_GET['dumpWorkspace'] == 'afterBuild') {
	exec($cmd);
	
	header('Content-Type: application/x-bzip-compressed-tar');
	header('Content-Disposition: attachment; filename="'.$appName.'.'.date('Y-m-d').'.tbz"');
	chdir($tmpPath);
	passthru("tar -cjf - ./");
	exec("rm -R $tmpPath");
	exit();
}
else {
	passthru("$cmd 2>&1", $cmdStatus);
}

Benchmark::mark("CMD finished: exitCode=$cmdStatus");

// import build
if($cmdStatus == 0) {	
	$buildTmpPath = "$tmpPath/build";
	Benchmark::mark("importing $buildTmpPath");
	
	$importResults = Emergence_FS::importTree($buildTmpPath, 'site-root/js/pages');
	Benchmark::mark("imported files: ".http_build_query($importResults));
	
	if(!empty($_GET['archive'])) {
		Benchmark::mark("importing $archiveTmpPath to $archivePath");
		
		$importResults = Emergence_FS::importTree($archiveTmpPath, $archivePath);
		Benchmark::mark("imported files: ".http_build_query($importResults));
	}
}


// clean up
if(empty($_GET['leaveWorkspace'])) {
	exec("rm -R $tmpPath");
	Benchmark::mark("erased $tmpPath");
}