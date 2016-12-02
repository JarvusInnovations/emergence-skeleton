{extends "design.tpl"}

{block title}{$path|escape} &mdash; {$dwoo.parent}{/block}

{block nav}
    {$activeSection = 'logs'}
    {$dwoo.parent}
{/block}

{block breadcrumbs}
    <li><a href="/site-admin/logs">Logs</a></li>
    <li class="active">{$path|escape}</li>
{/block}

{block "content"}
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <div class="btn-group">
                <a href="?{refill_query download=raw}" class='btn btn-primary'>
                    <i class="glyphicon glyphicon-download"></i>
                    Download
                </a>
            </div>
        </div>
        <h1>Site Log: <code>{$path|escape}</code></h1>
    </div>

    <em>Showing last {$lines|number_format} lines (<a href="?lines={$lines * 10}">more&hellip;</a>):</em>
    <pre>{$tail|escape}</pre>
{/block}