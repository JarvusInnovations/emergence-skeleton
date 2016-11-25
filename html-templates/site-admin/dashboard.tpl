{extends "design.tpl"}

{block nav}
    {$activeSection = dashboard}
    {$dwoo.parent}
{/block}

{block content}
    <div class="page-header">
        <h1>Site Dashboard</h1>
    </div>

    <dl class="dl-horizontal">
        {foreach item=metric from=$metrics}
            <dt>{$metric.label|escape}</dt>
            <dd>
                {if $metric.link}
                    <a href="{$metric.link|escape}">
                {/if}

                {if is_int($metric.value)}
                    {$metric.value|number_format}
                {else}
                    {$metric.value|escape}
                {/if}

                {if $metric.link}
                    </a>
                {/if}
            </dd>
        {/foreach}
    </dl>
{/block}