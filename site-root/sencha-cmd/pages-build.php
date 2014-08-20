<?php

/**
 * This command builds optimized javascript bundles for the frontend of a website using Sencha CMD
 *
 * sencha-workspace/pages/src/Common.js is examined first, in and all its dependencies are output to site-root/js/pages/common.js
 * sencha-workspace/pages/src/page.*.js is examined next, and for each one the page class and all its dependencies are output to site-root/js/pages/*.js
 *
 * All classes included in common.js are excluded from each page's build, and if 2 or more pages are being built all classes shared by two or more of them
 * are also included in common.js and excluded from the page-specific builds.
 *
 * Page class files may indicate package dependencies with header comments in the following format:
 * ```
 * // @require-package my-package-name
 * ````
 * 
 * TODO:
 * - For packages required by pages, all files within all packages' overrides folder should be automatically included in that page's build
 *
 */

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);

if(empty($_GET['dumpWorkspace'])) {
    Benchmark::startLive();
}

// load build cfg
$buildConfig = Sencha::loadProperties(Site::resolvePath('sencha-workspace/pages/.sencha/workspace/sencha.cfg')->RealPath);
$framework = $buildConfig['pages.framework'];
$frameworkVersion = Sencha::normalizeFrameworkVersion($framework, $buildConfig['pages.framework.version']);
$cmdVersion = $buildConfig['workspace.cmd.version'];

if(!$framework) {
    die("app.framework not found in sencha.cfg");
}

// set paths
$pagesPath = 'sencha-workspace/pages';
$frameworkPath = "sencha-workspace/$framework-$frameworkVersion";

// get temporary directory and set paths
$tmpPath = Emergence_FS::getTmpDir();

Benchmark::mark("created tmp: $tmpPath");


// change into tmpPath
chdir($tmpPath);
Benchmark::mark("chdir to: $tmpPath");


// precache and write pages
$cachedFiles = Emergence_FS::cacheTree($pagesPath);
Benchmark::mark("precached $cachedFiles files in $pagesPath");
$exportResult = Emergence_FS::exportTree($pagesPath, '.');
Benchmark::mark("exported $pagesPath to .: ".http_build_query($exportResult));


// ... framework
$cachedFiles = Emergence_FS::cacheTree($frameworkPath);
Benchmark::mark("precached $cachedFiles files in $frameworkPath");

$exportResult = Emergence_FS::exportTree($frameworkPath, $framework);
Benchmark::mark("exported $frameworkPath to ./$framework: ".http_build_query($exportResult));

// build command and scan for dependencies and pages
$packages = array();
$pageNames = array();
$pageLoadCommands = array();
$pageBuildCommands = array();
$classPaths = !empty($buildConfig['workspace.classpath']) ? explode(',', $buildConfig['workspace.classpath']) : array();

foreach (glob('./src/page/*.js') AS $page) {
    $pageNames[] = $pageName = basename($page, '.js');
    $pageOverrides = array();
    $pagePackages = array_unique(Sencha::crawlRequiredPackages(Sencha::getRequiredPackagesForSourceFile($page)));

    // detect required packages
    $packages = array_merge($packages, $pagePackages);

    // analyze packages, export, add to classPath, and register overrides per-page
    foreach ($pagePackages AS $package) {
        foreach (array('src', 'overrides') AS $subPath) { 
            $packageSource = "sencha-workspace/packages/$package/$subPath";
            $packageDest = "./packages/$package/$subPath";
            Benchmark::mark("importing package: $package from $packageSource");
        
            $cachedFiles = Emergence_FS::cacheTree($packageSource);
            Benchmark::mark("precached $cachedFiles files in $packageSource");
        
            $exportResult = Emergence_FS::exportTree($packageSource, $packageDest);
            Benchmark::mark("exported $packageSource to $packageDest: ".http_build_query($exportResult));
        
            $classPaths[] = "./packages/$package/$subPath";
            
            if ($subPath == 'overrides') {
                $pageOverrides[] = "include -recursive -file packages/$package/$subPath";
            }
        }
    }

    $pageLoadCommands[] =
        "union -recursive -class Site.page.$pageName"
        .( count($pageOverrides) ? ' and '.implode(' and ', $pageOverrides) : '')
        ." and save $pageName";

    $pageBuildCommands[] = "restore $pageName and exclude -set common and concat -yui ./build/$pageName.js";
}


// eliminate duplicate packages between pages
$packages = array_unique($packages);


// write any libraries from classpath
Benchmark::mark("crawling packages for classpaths");
$classPaths = array_merge($classPaths, Sencha::aggregateClassPathsForPackages($packages));

Benchmark::mark("processing all classpaths");
foreach($classPaths AS &$classPath) {
    if(strpos($classPath, '${workspace.dir}/x/') === 0) {
        $extensionPath = substr($classPath, 19);
        $classPathSource = "ext-library/$extensionPath";
        $classPath = "./x/$extensionPath";
        Benchmark::mark("importing classPathSource: $classPathSource");

        $cachedFiles = Emergence_FS::cacheTree($classPathSource);
        Benchmark::mark("precached $cachedFiles files in $classPathSource");

        $sourceNode = Site::resolvePath($classPathSource);

        if (is_a($sourceNode, SiteFile)) {
            mkdir(dirname($classPath), 0777, true);
            copy($sourceNode->RealPath, $classPath);
            Benchmark::mark("copied file $classPathSource to $classPath");
        } else {
            $exportResult = Emergence_FS::exportTree($classPathSource, $classPath);
            Benchmark::mark("exported $classPathSource to $classPath: ".http_build_query($exportResult));
        }
    }
}


// prepare cmd
$classPaths[] = 'src';
$cmd = Sencha::buildCmd(
    $cmdVersion
    ,"-sdk ./$framework"
    ,'compile'
        ,"-classpath=".implode(',', array_unique($classPaths))
        
        // start common.js with bootstrap, the rest will be appended later
        ,'union -class Ext.Boot and concat -yui ./build/common.js'

        // start with Site.Common and all its dependencies, store in set common
        ,'and union -recursive -class Site.Common'
        ,'and save common'

        // if there's at least one page...
        ,count($pageLoadCommands)
            ?
                // create a set for each Site.page.* class and its dependencies
                'and ' . join(' and ', $pageLoadCommands)

                // switch back to the common set
                .' and restore common'
            : ''

        // if there is more than one page being built for, add any dependencies that two or more share to the common set
        // if not, just switch back to the common set
        ,count($pageNames) > 1
            ?
                'and intersect -min=2 -set ' . join(',', $pageNames)
                .' and include -set common'
                .' and exclude -namespace Site.page'
                .' and save common'
            : 'and restore common'

        // output the common set to common.js
        ,"and concat -yui -append ./build/common.js"

        // if there's at least one page...
        ,count($pageBuildCommands)
            ? 'and ' . join(' and ', $pageBuildCommands)
            : ''
);
Benchmark::mark("running CMD: $cmd");

// optionally dump workspace and exit
if(!empty($_GET['dumpWorkspace']) && $_GET['dumpWorkspace'] != 'afterBuild') {
    header('Content-Type: application/x-bzip-compressed-tar');
    header('Content-Disposition: attachment; filename="'.$appName.'.'.date('Y-m-d').'.tbz"');
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
    Benchmark::mark("importing ./build");

    $importResults = Emergence_FS::importTree('build', 'site-root/js/pages');
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