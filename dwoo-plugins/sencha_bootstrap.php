<?php

function Dwoo_Plugin_sencha_bootstrap(Dwoo_Core $dwoo, $App = null, $classPaths = array(), $packages = array(), $patchLoader = true, $framework = 'ext', $frameworkVersion = null)
{
    // retrieve app if available
    if (!$App) {
        $App = $dwoo->data['App'];
    }

    // if app provided, load classpaths and packages
    if ($App) {
        $framework = $App->getFramework();
        $frameworkVersion = $App->getFrameworkVersion();
        $appPath = 'sencha-workspace/'.$App->getName();

        // include classpath files
        $classPaths = array_merge($classPaths, explode(',', $App->getBuildCfg('app.classpath')));

        // merge app's required packages into packages list
        if (is_array($appPackages = $App->getAppCfg('requires'))) {
            $packages = array_merge($packages, $appPackages);
        }

        // add theme to packages list
        if ($themeName = $App->getBuildCfg('app.theme')) {
            $packages[] = $themeName;
        }
    }

    // apply default framework version and normalize
    if (!$frameworkVersion) {
        $frameworkVersion = Sencha::$frameworks[$framework]['defaultVersion'];
    }
    
    $frameworkVersion = Sencha::normalizeFrameworkVersion($framework, $frameworkVersion);
    $frameworkPath = "sencha-workspace/$framework-$frameworkVersion";

    // add paths for packages
    foreach (array_unique($packages) AS $packageName) {
        $packagePath = "sencha-workspace/packages/$packageName";
        array_push($classPaths, "$packagePath/src", "$packagePath/overrides");
    }

    // build list of all selected collections, resolving CMD variables and children
    $srcCollections = array();
    foreach ($classPaths AS $classPath) {
        if (strpos($classPath, '${workspace.dir}/x/') === 0) {
            $classPath = 'ext-library/' . substr($classPath, 19);
        } elseif(strpos($classPath, '${app.dir}/') === 0) {
            $classPath = $appPath . substr($classPath, 10);
        } elseif(strpos($classPath, '${ext.dir}/') === 0) {
            $classPath = $frameworkPath . substr($classPath, 10);
        } elseif(strpos($classPath, '${touch.dir}/') === 0) {
            $classPath = $frameworkPath . substr($classPath, 12);
        }

        try {
            $tree = Emergence_FS::getTree($classPath);
            $srcCollections = array_merge($srcCollections, array_keys($tree));
        } catch (Exception $e) {
            continue;
        }
    }

    // get files
    if (count($srcCollections)) {
        $sources = DB::allRecords(
            'SELECT'
                .' f2.SHA1'
                .',CONCAT('
                    .'('
                        .'SELECT GROUP_CONCAT(parent.Handle ORDER BY parent.PosLeft SEPARATOR "/")'
                        .' FROM `%2$s` AS node, `%2$s` AS parent'
                        .' WHERE node.PosLeft BETWEEN parent.PosLeft AND parent.PosRight AND node.ID = f2.CollectionID'
                    .')'
                    .',"/"'
                    .',f2.Handle'
                .') AS Path'
                .' FROM ('
                    .' SELECT MAX(f1.ID) AS ID'
                    .' FROM `%1$s` f1'
                    .' WHERE CollectionID IN (%3$s)'
                    .' GROUP BY f1.CollectionID, f1.Handle'
                .') AS lastestFiles'
                .' LEFT JOIN `%1$s` f2 USING (ID)'
                .' WHERE f2.Status = "Normal" AND f2.Type = "application/javascript"'
            ,array(
                SiteFile::$tableName
                ,SiteCollection::$tableName
                ,join(',', $srcCollections)
            )
        );
    }

    // skip patching loader if manifest will be empty
    if (empty($sources)) {
        return '';
    }

    // process all source files and build manifest and list of classes to automatically load
    $manifest = array(
        'Ext' => "/app/$framework-$frameworkVersion/src"
    );
    $autoLoadPaths = array();

    foreach ($sources AS &$source) {
        $path = $source['Path'];
        $autoLoad = false;

        // rewrite path to canonican external URL
        if ($appPath && strpos($path, "$appPath/") === 0) {
            $path = '/app/'.substr($path, 17);
        } elseif (strpos($path, 'ext-library/') === 0) {
            $path = '/app/x'.substr($path, 11);
        } elseif (strpos($path, 'sencha-workspace/packages/') === 0) {
            $path = '/app/'.substr($path, 17);

            // package overrides should automatically be loaded
            if (substr($path, strpos($path, '/', 26), 11) == '/overrides/') {
                $autoLoad = true;
            }
        } elseif (strpos($path, 'sencha-workspace/pages/') === 0) {
            $path = '/app/'.substr($source['Path'], 17);
        } elseif (strpos($path, $frameworkPath) === 0) {
            $path = '/app/'.substr($source['Path'], 17);
        } else {
            // this class was not in a recognized externally loadable collection
            continue;
        }

        // discover class name
        $sourceCacheKey = "sencha-class-name/$source[SHA1]";

        if (!$source['Class'] = Cache::fetch($sourceCacheKey)) {
            $sourceNode = Site::resolvePath($source['Path']);
            $sourceReadHandle = $sourceNode->get();

            while (($line = fgets($sourceReadHandle, 4096)) !== false) {
                if (preg_match('/\s*Ext\.define\(([\'"])([^\'"]+)\1/i', $line, $matches)) {
                    $source['Class'] = $matches[2];
                    break;
                }
            }

            fclose($sourceReadHandle);

            // cache class name
            Cache::store($sourceCacheKey, $source['Class']);
        }

        // skip if class name could not be determined
        if (!$source['Class']) {
            continue;
        }

        // apply fingerprint signature to path
        $path = "$path?_sha1=$source[SHA1]";

        // map class name to path
        $manifest[$source['Class']] = $path;

        // add path to autoLoad list
        if ($autoLoad) {
            $autoLoadPaths[] = $path;
        }
    }

    // output loader patch and manifest
    return
        '<script type="text/javascript">(function(){'
        
            .(
                $patchLoader ?
                    'var origLoadScript = Ext.Loader.loadScript'
                        .',origLoadScriptFile = Ext.Loader.loadScriptFile'
                        .',dcParam = Ext.Loader.getConfig("disableCachingParam")'
                        .',now = Ext.Date.now();'
        
                    .'function _versionScriptUrl(url) {'
                        .'if (url[0] != "/") {'
                            .'url = window.location.pathname + url;'
                            .'while (url.match(/\/\.\.\//)) url = url.replace(/\/[^\/]+\/\.\./g, "");'
                        .'}'
        
                        .'if(!url.match(/\?_sha1=/)) {'
                            .'console.warn("Fingerprinted URL not found for %o, it will be loaded with a cache-buster", url);'
                            .'url += "?" + dcParam + "=" + now;'
                        .'}'
        
                        .'return url;'
                    .'}'
        
                    .'Ext.Loader.loadScript = function(options) {'
                        .'if (typeof options == "string") {'
                            .'options = _versionScriptUrl(options);'
                        .'} else {'
                            .'options.url = _versionScriptUrl(options.url);'
                        .'}'
                        .'origLoadScript.call(Ext.Loader, options);'
                    .'};'
        
                    .'Ext.Loader.loadScriptFile = function(url, onLoad, onError, scope, synchronous) {'
                        .'origLoadScriptFile.call(Ext.Loader, _versionScriptUrl(url), onLoad, onError, scope, synchronous);'
                    .'};'
        
                    .'Ext.Loader.setConfig("disableCaching", false);'
                :
                    ''
            )

            .'Ext.Loader.addClassPathMappings('.json_encode($manifest).');'
            .( count($autoLoadPaths) ? 'Ext.Array.each('.json_encode($autoLoadPaths).', origLoadScript);' : '' )
        .'})()</script>';
}