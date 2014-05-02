{extends app/ext.tpl}

{block js-app-devenv}
    {$suppressBootstrap = true}
    {$dwoo.parent}

    <script type="text/javascript">
        Ext.Loader.setPath('Ext.ux', '/app/{$App->getName()}/sdk/examples/ux');
    </script>
{/block}