{template sourceStatusCls status}{strip}
    {if $status == 'uninitialized'}
        default
    {elseif $status == 'clean'}
        success
    {elseif $status == 'commit-staged'}
        info
    {elseif $status == 'dirty'}
        warning
    {else}
        default
    {/if}
{/strip}{/template}