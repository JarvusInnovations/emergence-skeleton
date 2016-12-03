{extends design.tpl}

{block nav}
    {$activeSection = 'migrations'}
    {$dwoo.parent}
{/block}

{block content}
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <form method="POST" action="/site-admin/migrations/refresh" class="btn-group">
                <button type="submit" class="btn btn-default">Refresh Inherited Migrations</button>
            </form>
        </div>

        <h1>Migrations</h1>
    </div>

    <table class="table table-striped">
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
                <tr class="{tif $migration.status == executed ? success} {tif $migration.status == skipped ? info} {tif $migration.status == started ? warning} {tif $migration.status == failed ? danger}">
                    <td class="migration-id"><a href="/site-admin/migrations/{$migration.key|escape}">{$migration.key|escape}</a><br><small>SHA1: {$migration.sha1}</td>
                    <td class="migration-status">{$migration.status}</td>
                    <td class="migration-timestamp">{$migration.executed}</td>
                    <td class="migration-action">
                        {if $migration.status == 'new'}
                            <form class="execute-migration" action="/site-admin/migrations/{$migration.key|escape}" method="POST">
                                <button type="submit" class="btn btn-primary">Execute</button>
                            </form>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/block}