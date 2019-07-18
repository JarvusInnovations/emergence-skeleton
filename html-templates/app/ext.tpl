<!DOCTYPE html>
{$appName = $App->getName()}
{$appTheme = default($.get.theme, default($App->getAppCfg('theme'), $App->getBuildCfg('app.theme')))}
{$jsBuildPath = tif($App->getAsset("build/$mode/app.js"), "build/$mode/app.js", "build/$mode/all-classes.js")}
{$cssMode = tif($mode == 'development' ? 'production' : $mode)}
{$cssBuildPath = tif($appTheme, "build/$cssMode/resources/$appName-all.css", "build/$cssMode/resources/default/app.css")}
<html>
    <head>
        {block meta}
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

            <title>{if $title}{$title}{else}{$appName}-{$mode}{/if}</title>
        {/block}

        {block base}{/block}

        {block css-loader}{/block}

        {block css-app}
            {$cssBuildNode = $App->getAsset($cssBuildPath)}
            {if $cssBuildNode}
                <?php
                    $this->scope['cssBuildPaths'] = [];

                    // attempt to parse out a import-only main css file
                    if ($this->scope['cssBuildNode']->Size < 512) {
                        $basePath = dirname($this->scope['cssBuildPath']);
                        $contents = file_get_contents($this->scope['cssBuildNode']->RealPath);

                        foreach (file($this->scope['cssBuildNode']->RealPath) as $line) {
                            $line = trim($line);

                            if (!$line) {
                                continue;
                            }

                            if (!preg_match('/^@import \'([^\']+)\';$/', $line, $matches)) {
                                $this->scope['cssBuildPaths'] = [];
                                break;
                            }

                            $this->scope['cssBuildPaths'][] = $basePath . '/' . $matches[1];
                        }
                    }

                    if (!count($this->scope['cssBuildPaths'])) {
                        $this->scope['cssBuildPaths'][] = $this->scope['cssBuildPath'];
                    }
                ?>

                {foreach item=cssPath from=$cssBuildPaths}
                    <link rel="stylesheet" type="text/css" href="{$App->getVersionedPath($cssPath)}" />
                {/foreach}
            {elseif $appTheme}
                <link rel="stylesheet" type="text/css" href="{$App->getVersionedPath(cat('sdk/packages/$appTheme/build/resources/' $appTheme '-all.css'))}" />
            {else}
                <link rel="stylesheet" type="text/css" href="{$App->getVersionedPath('sdk/resources/css/ext-all.css')}" />
            {/if}
        {/block}
    </head>

    <body class="{block body-class}loading{/block}">
        {block body}
        {/block}

        {block js-data}
            <script type="text/javascript">
                var SiteEnvironment = SiteEnvironment || { };
                SiteEnvironment.user = {JSON::translateObjects($.User)|json_encode};
                SiteEnvironment.appName = {$App->getName()|json_encode};
                SiteEnvironment.appMode = {$mode|json_encode};
                SiteEnvironment.appBaseUrl = '/app/{$App->getName()}/{tif $mode == production || $mode == testing ? "build/$mode/"}';
            </script>
        {/block}

        {block js-app}
            {if $mode != 'development' && $App->getAsset($jsBuildPath)}
                {$jsEntryPath = $jsBuildPath}

                {if $App->getAsset('.sencha/app/Boot.js')}
                    <script type="text/javascript" src="{$App->getVersionedPath('.sencha/app/Boot.js')}"></script>
                {/if}
            {else}
                {$jsEntryPath = tif($App->getAsset('app.js') ? 'app.js' : 'app/app.js')}

                {block js-app-devenv}
                    {$frameworkBuild = 'ext'}

                    {if $.get.frameworkBuild != core}
                        {$frameworkBuild .= '-all'}
                    {/if}

                    {if $mode == 'development' && $.get.frameworkBuild != allmin}
                        {$frameworkBuild .= tif($App->getAsset("sdk/$frameworkBuild-dev.js") ? '-dev' : '-debug')}
                    {/if}

                    {$frameworkPath = cat('sdk/build/' $frameworkBuild '.js')}
                    {if !$App->getAsset($frameworkPath)}
                        {$frameworkPath = cat('sdk/' $frameworkBuild '.js')}
                    {/if}

                    <script type="text/javascript" src="{$App->getVersionedPath($frameworkPath)}"></script>

                    {if $appTheme}
                        {$workspaceThemeIncludePath = cat('packages/$appTheme/build/' $appTheme '.js')}
                        {$sdkThemeIncludePath = cat('sdk/packages/$appTheme/build/' $appTheme '.js')}

                        {if $App->getAsset($workspaceThemeIncludePath)}
                            <script type="text/javascript" src="{$App->getVersionedPath($workspaceThemeIncludePath)}"></script>
                        {elseif $App->getAsset($sdkThemeIncludePath)}
                            <script type="text/javascript" src="{$App->getVersionedPath($sdkThemeIncludePath)}"></script>
                        {/if}
                    {/if}

                    {sencha_bootstrap}
                {/block}
            {/if}

            {block js-app-local}
                <script type="text/javascript" src="{$App->getVersionedPath($jsEntryPath)}"></script>
            {/block}

            <script>
            (function() {
                var resolveResource = Ext.resolveResource,
                    appBaseUrl = (window.SiteEnvironment || { }).appBaseUrl;

                if (resolveResource && appBaseUrl) {
                    Ext.resolveResource = function(url) {
                        url = resolveResource(url);

                        if (url.charAt(0) != '/') {
                            url = appBaseUrl + url;
                        }

                        return url;
                    };
                }
            })();
            </script>

            {block js-app-remote}
                {foreach item=script from=$App->getAppCfg('js')}
                    {if $script.remote}
                        <script src="{$script.path|escape}"></script>
                    {/if}
                {/foreach}
            {/block}
        {/block}

        {block "js-analytics"}
            {include includes/site.analytics.tpl}
        {/block}
    </body>
</html>