{extends "task.tpl"}

{block content}
    <form method="POST" class="panel panel-default">
        {if $title}
            <div class="panel-heading"><h1 class="panel-title">{$title|escape}</h1></div>
        {/if}

        <div class="panel-body">
            <pre>{$code|escape}</pre>
        </div>

        <div class="panel-footer">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default" onclick="javascript:history.go(-1);">Cancel</button>
                <button type="submit" class="btn btn-primary">Execute</button>
            </div>
        </div>
    </form>
{/block}