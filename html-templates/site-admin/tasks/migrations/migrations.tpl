{extends "task.tpl"}

{block content}
    <div class="panel panel-default">
        <div class="panel-heading">
            <form method="POST" action="{$.task.baseUrl}/refresh" class="btn-group btn-group-xs pull-right">
                <button type="submit" class="btn btn-default">Refresh Inherited Migrations</button>
            </form>

            <h1 class="panel-title">Migrations</h1>
        </div>

        <table class="panel-body table table-striped">
            <thead>
                <tr>
                    <th scope="col">Migration</th>
                    <th scope="col">Status</th>
                    <th scope="col">Timestamp</th>
                    <th scope="col"></th>
                </tr>
            </thead>

            <tbody>
                {foreach item=migration from=$migrations}
                    <tr class="{tif $migration.status == executed ? success} {tif $migration.status == skipped ? info} {tif $migration.status == started ? info}">
                        <td class="migration-id"><a href="{$.task.baseUrl}/{$migration.key|escape}">{$migration.key|escape}</a><br><small>SHA1: {$migration.sha1}</td>
                        <td class="migration-status">{$migration.status}</td>
                        <td class="migration-timestamp">{$migration.executed}</td>
                        <td class="migration-action">
                            {if $migration.status == 'new'}
                                <form class="execute-migration" action="{$.task.baseUrl}/{$migration.key|escape}" method="POST">
                                    <button type="submit" class="btn btn-primary">Execute</button>
                                </form>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{/block}