{extends "webapps/sencha.tpl"}

{block "before-all"}
    {$app = Emergence\CMS\WebApp::load()}
    {$dwoo.parent}
{/block}

{block "js-data"}
    {$dwoo.parent}
    <script type="text/javascript">
        window.SiteEnvironment.cmsContent = {tif $data ? JSON::translateObjects($data, false, 'tags,items,Author,Context.recordURL,Context.recordTitle')|json_encode : 'null'};
    </script>
{/block}
