{extends migrations.tpl}

{block title}{$migration.key} &mdash; {$dwoo.parent}{/block}

{block breadcrumbs}
    <li><a href="/site-admin/migrations">Migrations</a></li>
    <li class="active">{$migration.key}</li>
{/block}

{block content}
    <div class="panel panel-default {tif $migration.status == executed ? 'panel-success'} {tif $migration.status == skipped ? 'panel-info'} {tif $migration.status == started ? 'panel-warning'} {tif $migration.status == failed ? 'panel-danger'}">
        <div class="panel-heading">
            {if $migration.status == new}
                <form action="/site-admin/migrations/{$migration.key|escape}" method="POST" class="btn-group btn-group-xs pull-right">
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