<?php

$GLOBALS['Session']->requireAccountLevel('Developer');







/**
 * Configuration
 */
    $defaultExclude = [
        "#/\\.sass-cache(/|$)#"
        ,"#/\\.sencha-backup(/|$)#"
        ,"#/\\.emergence(/|$)#"
        ,"#/temp(/|$)#"
    ];







/**
 * Setup environment
 */
    set_time_limit(0);
    Site::$debug = !empty($_REQUEST['debug']);

    if (empty($_GET['dumpWorkspace'])) {
        Benchmark::startLive();
    }








/**
 * Load top-level components
 */
    // get build type
    if (empty($_REQUEST['buildType'])) {
        $buildType = 'production';
    } else {
        $buildType = $_REQUEST['buildType'];
    }
    Benchmark::mark("set buildType: $buildType");


    // get package
    if (empty($_REQUEST['name'])) {
        die('Parameter name required');
    }

    $package = Jarvus\Sencha\WorkspacePackage::load($_REQUEST['name']);

    if (!$package) {
        throw new \Exception('Failed to load package');
    }

    Benchmark::mark("loaded package: $package");


    // get framework
    $framework = $package->getFramework();

    if (!$framework) {
        throw new \Exception('Failed to load framework, ensure app.framework.version is set');
    }

    Benchmark::mark("loaded framework: $framework");


    // load CMD
    $cmd = $package->getCmd() ?: Jarvus\Sencha\Cmd::getLatest();

    if (!$cmd) {
        throw new \Exception('Failed to load CMD');
    }

    Benchmark::mark("loaded cmd: $cmd");


    // get app-level classpath
    $classPaths = $package->getClassPaths();
    Benchmark::mark('loaded classPaths:'.PHP_EOL.implode(PHP_EOL, $classPaths));


    // get packages
    $packages = $package->getAllRequiredPackages();
    Benchmark::mark('loaded required packages:'.PHP_EOL.implode(PHP_EOL, $packages));









/**
 * Builds paths and create temporary directories
 */
    // TODO: analyze which are still used:
    // set paths
    $workspacePath = 'sencha-workspace';
    $workspaceConfigPath = "$workspacePath/.sencha";
    $packagePath = $package->getVirtualPath();


    // get temporary directory and set paths
    $tmpPath = Emergence_FS::getTmpDir();
    $frameworkTmpPath = "$tmpPath/$framework";
    $workspaceConfigTmpPath = "$tmpPath/.sencha";
    $packageTmpPath = "$tmpPath/packages/$package";
    $packagesTmpPath = "$tmpPath/packages";
    $scratchTmpPath = "$tmpPath/temp";
    $libraryTmpPath = "$tmpPath/x";

    Benchmark::mark("created tmp: $tmpPath");


    // get path to framework on disk
    $frameworkPhysicalPath = $framework->getPhysicalPath();
    Benchmark::mark("got physical path to framework: $frameworkPhysicalPath");








/**
 * Copy files into temporary build workspace
 */
    // if (stat($frameworkPhysicalPath)['dev'] == stat($tmpPath)['dev']) {
    //     // copy framework w/ hardlinks if paths are on the same device
    //     exec("cp -al $frameworkPhysicalPath $frameworkTmpPath");
    //     Benchmark::mark("copied framework: cp -al $frameworkPhysicalPath $frameworkTmpPath");
    // } else {
    //     // make full copy because hardlines don't work across devices
    //     exec("cp -a $frameworkPhysicalPath $frameworkTmpPath");
    //     Benchmark::mark("copied framework: cp -a $frameworkPhysicalPath $frameworkTmpPath");
    // }

    // precache and write workspace config
    $cachedFiles = Emergence_FS::cacheTree($workspaceConfigPath);
    Benchmark::mark("precached $cachedFiles files in $workspaceConfigPath");
    $exportResult = Emergence_FS::exportTree($workspaceConfigPath, $workspaceConfigTmpPath);
    Benchmark::mark("exported $workspaceConfigPath to $workspaceConfigTmpPath: ".http_build_query($exportResult));


    // framework -- doesn't need to be written as long as patching ${framework}.dir into sencha.cfg keeps working
#    $framework->writeToDisk("$tmpPath/$framework");
#    Benchmark::mark("wrote $framework to $tmpPath/$framework");


    // precache and write package
    $cachedFiles = Emergence_FS::cacheTree($packagePath);
    Benchmark::mark("precached $cachedFiles files in $packagePath");
    $exportResult = Emergence_FS::exportTree($packagePath, $packageTmpPath);
    Benchmark::mark("exported $appPath to $packageTmpPath: ".http_build_query($exportResult));



    // copy packages to workspace (except framework packages)
    foreach ($packages AS $packageName => $package) {
        if ($package instanceof Jarvus\Sencha\FrameworkPackage) {
            continue;
        }

        $package->writeToDisk("$packagesTmpPath/$packageName");
        Benchmark::mark("wrote package $package to $packagesTmpPath/$packageName");
    }









/**
 * Execute build
 */
    // change into app's directory
    chdir($packageTmpPath);
    Benchmark::mark("chdir to: $packageTmpPath");


    // prepare cmd
    $shellCommand = $cmd->buildShellCommand(
        'ant',
            // preset build directory parameters
            "-Dext.dir=$frameworkPhysicalPath",
            "-Dbuild.temp.dir=$scratchTmpPath",

            // optional closure path
            class_exists('Jarvus\Closure\Compiler') && ($closureJarPath = Jarvus\Closure\Compiler::getJarPath()) ? "-Dbuild.compression.closure.jar=$closureJarPath" : null,

        // ant targets
        'build'
    );
    Benchmark::mark("running CMD: $shellCommand");



    // optionally dump workspace and exit
    if (!empty($_GET['dumpWorkspace']) && $_GET['dumpWorkspace'] != 'afterBuild') {
        header('Content-Type: application/x-bzip-compressed-tar');
        header('Content-Disposition: attachment; filename="'.$app.'.'.date('Y-m-d').'.tbz"');
        chdir($tmpPath);
        passthru("tar -cjf - ./");
        exec("rm -R $tmpPath");
        exit();
    }


    // execute CMD
    //  - optionally dump workspace and exit
    if (!empty($_GET['dumpWorkspace']) && $_GET['dumpWorkspace'] == 'afterBuild') {
        exec($shellCommand);

        header('Content-Type: application/x-bzip-compressed-tar');
        header('Content-Disposition: attachment; filename="'.$app.'.'.date('Y-m-d').'.tbz"');
        chdir($tmpPath);
        passthru("tar -cjf - ./");
        exec("rm -R $tmpPath");
        exit();
    } else {
        $pipes = [];
        $process = proc_open(
            "$shellCommand 2>&1",
            [
                1 => ['pipe', 'wb'] // STDOUT
            ],
            $pipes
        );

        while ($s = fgets($pipes[1])) {
            print($s);
            flush();
        }

        fclose($pipes[1]);
        $cmdStatus = proc_close($process);
    }

    Benchmark::mark("CMD finished: exitCode=$cmdStatus");





/**
 * Import build
 */
    if ($cmdStatus == 0) {
        Benchmark::mark("importing $packageTmpPath/build");

        $importResults = Emergence_FS::importTree("$packageTmpPath/build", "$packagePath/build", [
            'exclude' => $defaultExclude
        ]);

        Benchmark::mark("imported files: ".http_build_query($importResults));
    }






/**
 * Clean up
 */
    if (empty($_GET['leaveWorkspace'])) {
        exec("rm -R $tmpPath");
        Benchmark::mark("erased $tmpPath");
    }
