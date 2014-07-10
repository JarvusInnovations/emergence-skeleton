<!DOCTYPE html>
{$appName = $App->getName()}
{$appTheme = default($.get.theme, $App->getBuildCfg('app.theme'))}
{$jsBuildPath = tif($App->getAsset("build/$mode/app.js"), "build/$mode/app.js", "build/$mode/all-classes.js")}
{$cssMode = tif($mode == 'development' ? 'production' : $mode)}
{$cssBuildPath = tif($appTheme, "build/$cssMode/resources/$appName-all.css", "build/$cssMode/resources/default/app.css")}
<html>
    <head>
        {block meta}
            <meta charset="UTF-8">
            <title>{if $title}{$title}{else}{$appName}-{$mode}{/if}</title>
        {/block}

        {block js-data}
            <script type="text/javascript">
                var SiteEnvironment = SiteEnvironment || { };
                SiteEnvironment.user = {$.User->getData()|json_encode};
                SiteEnvironment.appName = {$App->getName()|json_encode};
                SiteEnvironment.appMode = {$mode|json_encode};
                SiteEnvironment.appBaseUrl = '/app/{$App->getName()}/{tif $mode == production || $mode == testing ? "build/$mode/"}';
            </script>
        {/block}

        {block js-app}
            {if $mode == 'development' || !$App->getAsset($jsBuildPath)}
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

                    {sencha_bootstrap}
                {/block}

                <script type="text/javascript" src="{tif $App->getAsset('app.js') ? $App->getVersionedPath('app.js') : $App->getVersionedPath('app/app.js')}"></script>
            {else}
                <script type="text/javascript" src="{$App->getVersionedPath($jsBuildPath)}"></script>
            {/if}
        {/block}

        {block css-app}
            {if $App->getAsset($cssBuildPath)}
                <link rel="stylesheet" type="text/css" href="{$App->getVersionedPath($cssBuildPath)}" />
            {elseif $appTheme}
                <link rel="stylesheet" type="text/css" href="{$App->getVersionedPath(cat('sdk/packages/$appTheme/build/resources/' $appTheme '-all.css'))}" />
                <script type="text/javascript" src="{$App->getVersionedPath(cat('sdk/packages/$appTheme/build/' $appTheme '.js'))}"></script>
            {else}
                <link rel="stylesheet" type="text/css" href="{$App->getVersionedPath('sdk/resources/css/ext-all.css')}" />
            {/if}
        {/block}
    </head>

    {block body}
        <body class="loading"></body>
    {/block}
</html>