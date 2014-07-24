{extends designs/site.tpl}

{block "css"}
    <link rel="stylesheet" type="text/css" href="{Sencha_App::getByName('ContentEditor')->getVersionedPath('build/production/resources/ContentEditor-all.css')}" />

    {$dwoo.parent}
{/block}

{block js-bottom}
    <script type="text/javascript">
        window.SiteUser = {$.User->getData()|json_encode};
        window.ContentData = {tif $data ? JSON::translateObjects($data->getDetails(array('tags','items','Author','Context')))|json_encode : 'null'};
    </script>

    {$dwoo.parent}

    {if $.get.jsdebug}
        {sencha_bootstrap patchLoader=false packages=array('emergence-cms', 'ext-theme-crisp-touch')}
    {else}
        <script src="{Site::getVersionedRootUrl('js/pages/ContentEditor.js')}"></script>
    {/if}
    
    {jsmin "markdown.js"}

    <script>
        Ext.require('Site.page.ContentEditor');
    </script>
{/block}

{block content}
    <div id='contentEditorCt'>Loading content editor&hellip;</div>
{/block}