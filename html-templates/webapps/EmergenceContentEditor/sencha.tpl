{extends designs/site.tpl}

{block "before-all"}
    {$app = Emergence\CMS\WebApp::load()}
    {$dwoo.parent}
{/block}

{block "meta-rendering"}
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10, user-scalable=yes">
{/block}

{block "css"}
    {$dwoo.parent}
    {$app->buildCssMarkup()}
{/block}

{block content}
    <div id="app-viewport">Loading content editor&hellip;</div>
{/block}

{block "js-bottom"}
    {$dwoo.parent}

    {$app->buildDataMarkup()}

    <script type="text/javascript">
        window.SiteEnvironment.cmsContent = {tif $data ? JSON::translateObjects($data, false, 'tags,items,Author,Context.recordURL,Context.recordTitle')|json_encode : 'null'};
    </script>

    {$app->buildJsMarkup()}
{/block}