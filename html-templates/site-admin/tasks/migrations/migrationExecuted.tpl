{extends migration.tpl}

{block title}Executed {$dwoo.parent}{/block}

{block breadcrumbs}
    {$crumbTrail = array($migration.key => "$.task.baseUrl/$migration.key", Results => false)}
    {$dwoo.parent}
{/block}

{block content}

    {if $migration.status == executed}
        <div class="alert alert-success" role="alert">Migration executed</div>
    {elseif $migration.status == skipped}
        <div class="alert alert-info" role="alert">Migration skipped</div>
    {/if}

    {$dwoo.parent}

    <div class="panel panel-default">
        <div class="panel-heading">Script output</div>
        <div class="panel-body">
            <samp class="panel-body">{$output|escape}</samp>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">Query Log</div>
        <table class="panel-body table table-striped">
            <thead>
                <tr>
                    <th scope="col">Query</th>
                    <th scope="col">Rows</th>
                    <th scope="col">Time</th>
                </tr>
            </thead>

            <tbody>
                {foreach item=entry from=$log}
                    <tr>
                        <td>{$entry.query|escape}</td>
                        <td>{$entry.affected_rows|default:entry.result_rows|number_format}</td>
                        <td>{$entry.time_duration_ms|number_format:2}ms</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{/block}