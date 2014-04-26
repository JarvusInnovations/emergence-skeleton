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

        {*block base}
            {if $mode == 'production' || $mode == 'testing'}
                <base href="/app/{$App->getName()}/build/{$mode}/">
            {else}
                <base href="/app/{$App->getName()}/">
            {/if}
        {/block*}

        {block js-data}
            <script type="text/javascript">window.SiteUser = {$.User->getData()|json_encode};</script>
        {/block}

        {block js-app}
            {if $mode == 'development' || !$App->getAsset($jsBuildPath)}
                {capture assign=frameworkPath}sdk/ext{tif $.get.frameworkBuild!=core ? '-all'}{tif $mode == 'development' && $.get.frameworkBuild != allmin ? '-dev'}.js{/capture}
                <script type="text/javascript" src="{$App->getVersionedPath($frameworkPath)}"></script>

                {sencha_preloader}

                {if !$suppressBootstrap}
                    <script type="text/javascript" src="{$App->getVersionedPath('bootstrap.js')}"></script>
                {/if}

                <script type="text/javascript">
                    Ext.Loader.setConfig({
                        enabled: true
                        ,paths: {
                            'Ext': '/app/{$App->getName()}/sdk/src'
                            ,'Ext.ux': '/app/{$App->getName()}/sdk/examples/ux'
                            ,'Emergence': '/app/{$App->getName()}/x/Emergence'
                            ,'Jarvus': '/app/{$App->getName()}/x/Jarvus'
                        }
                    });
                </script>

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