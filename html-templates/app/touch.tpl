<!DOCTYPE HTML>
<html {if $mode=='production'}manifest="/app/{$App->getName()}/cache.appcache"{/if} lang="en-US">
<head>
    {block meta}
        <meta charset="UTF-8">
        <title>{if $title}{$title}{else}{$App->getName()}-{$mode}{/if}</title>
    {/block}

    {block base}
        {if $mode == 'production' || $mode == 'testing'}
            <base href="/app/{$App->getName()}/build/{$mode}/">
        {else}
            <base href="/app/{$App->getName()}/">
        {/if}
    {/block}

    {block css-loader}
        <style type="text/css">
            html, body {
                height: 100%;
                background-color: {$loaderBgColor|default:"#1985D0"};
            }

            #appLoadingIndicator {
                position: absolute;
                top: 50%;
                margin-top: -15px;
                text-align: center;
                width: 100%;
                height: 30px;
                -webkit-animation-name: appLoadingIndicator;
                -webkit-animation-duration: 0.5s;
                -webkit-animation-iteration-count: infinite;
                -webkit-animation-direction: linear;
            }

            #appLoadingIndicator > * {
                background-color: {$loaderFgColor|default:"#FFFFFF"};
                display: inline-block;
                height: 30px;
                -webkit-border-radius: 15px;
                margin: 0 5px;
                width: 30px;
                opacity: 0.8;
            }

            @-webkit-keyframes appLoadingIndicator{
                0% {
                    opacity: 0.8
                }
                50% {
                    opacity: 0
                }
                100% {
                    opacity: 0.8
                }
            }
        </style>
    {/block}

    {block js-loader}
        {if $mode == 'production'}
            <script type="text/javascript">
                {$App->getMicroloader($mode)}
                Ext.blink({ "id":"{$App->getAppId()}" })
            </script>
        {/if}
    {/block}
</head>
<body>
    <div id="appLoadingIndicator">
        <div></div>
        <div></div>
        <div></div>
    </div>

    {block js-data}
    {/block}

    {block css-app}
        {if $mode != 'production'}
            {if !$App->getAsset('build/production/resources/css/app.css')}
                <link rel="stylesheet" href="{$App->getVersionedPath('sdk/resources/css/sencha-touch.css')}">
            {else}
                <link rel="stylesheet" href="{$App->getVersionedPath('build/production/resources/css/app.css')}">
            {/if}
        {/if}
    {/block}

    {block js-app}
            {if $mode != 'production'}
            {if $mode == 'development' || !$App->getAsset('build/production/app.js')}
                {capture assign=frameworkPath}sdk/sencha-touch{tif $.get.frameworkBuild!=core ? '-all'}{tif $mode == 'development' && $.get.frameworkBuild != allmin ? '-debug'}.js{/capture}
                <script type="text/javascript" src="{$App->getVersionedPath($frameworkPath)}"></script>
                <script type="text/javascript">
                    Ext.Loader.setPath({
                        'Ext': '/app/{$App->getName()}/sdk/src'
                        ,'Emergence': '/app/{$App->getName()}/x/Emergence'
                        ,'Jarvus': '/app/{$App->getName()}/x/Jarvus'
                    });
                </script>

                {sencha_preloader}

                {$scriptRoot = ''}
            {else}
                {$scriptRoot = 'build/production/'}
            {/if}

            {foreach item=script from=$App->getAppCfg('js')}
                {if !$script['x-bootstrap']}
                    <script type="text/javascript" src="{$App->getVersionedPath(cat($scriptRoot, $script.path))}"></script>
                {/if}
            {/foreach}
        {/if}

        {foreach item=script from=$App->getAppCfg('js')}
            {if $script.remote}
                <script src="{$script.path|escape}"></script>
            {/if}
        {/foreach}
    {/block}
</body>
</html>