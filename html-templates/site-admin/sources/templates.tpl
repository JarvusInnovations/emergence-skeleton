{template sourceStatusCls status}{strip}
    {if $status == 'uninitialized'}
        default
    {elseif $status == 'clean'}
        success
    {elseif $status == 'commit-staged'}
        info
    {elseif $status == 'dirty'}
        warning
    {elseif $status == 'behind'}
        info
    {elseif $status == 'ahead'}
        success
    {else}
        default
    {/if}
{/strip}{/template}