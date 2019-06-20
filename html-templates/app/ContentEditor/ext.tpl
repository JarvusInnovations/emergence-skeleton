{extends app/ext.tpl}

{block css-app}
    {* TODO: is  this still needed with new builds? *}
    {cssmin fonts/font-awesome.css}
    {$dwoo.parent}
{/block}