{extends task.tpl}

{block breadcrumbs}
    {$crumbTrail = $crumbTrail|default:array($migration.key => "$.task.baseUrl/$migration.key")}
    {$dwoo.parent}
{/block}

{block content}
    <div class="panel panel-default">
        <div class="panel-heading">
            {if $migration.status == new}
                <form action="{$.task.baseUrl}/{$migration.key|escape}" method="POST" class="btn-group btn-group-xs pull-right">
                    <button type="submit" class="btn btn-primary">Execute</button>
                </form>
            {/if}

            <h1 class="panel-title">Migration {$migration.key|escape}</h1>
        </div>

        <div class="panel-body">
            <dl class="dl-horizontal">
                {foreach item=value key=key from=$migration}
                    <dt>{$key|escape}</dt>
                    <dd>{$value|escape}</dd>
                {/foreach}
            </dl>
        </div>
    </div>
{/block}