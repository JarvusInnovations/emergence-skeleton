{extends "design.tpl"}

{block "content"}
    <div class="jumbotron">
    <h3>Server Mounts</h3>
    <div class="mounts">
        {foreach from=$Mounts item=Mount}
            <div class="mount">
                <div class="bar">
                    <div class="bar-label">{$Mount.MountPath}<span class="size">{$Mount.Used} / {$Mount.ThousandKBlocks}</span></div>
                    <div class="bar-fill" style="width: {$Mount.UsedPercentage}"></div>
                </div>
            </div>
        {/foreach}
    </div>
{/block}