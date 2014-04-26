{extends app/ext.tpl}

{block js-app}
    {$suppressBootstrap = true}
    {$dwoo.parent}
{/block}