{extends "design.tpl"}

{block "content"}
    <form method="POST" class="panel panel-{$statusClass|default:'default'}">
        {if $title}
            <div class="panel-heading"><h1 class="panel-title">{$title|escape}</h1></div>
        {/if}

        <div class="panel-body">
            {$message|escape|markdown}

            <a href="{$returnURL|default:"javascript:history.go(-1)"}" class="btn btn-default">{$returnLabel|default:"&laquo; Back"}</a>
        </div>
    </div>
{/block}