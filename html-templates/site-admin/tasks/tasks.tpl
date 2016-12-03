{extends "design.tpl"}

{block nav}
    {$activeSection = 'tasks'}
    {$dwoo.parent}
{/block}

{block content}
    <div class="page-header">
        <h1>Site Tasks</h1>
    </div>

    {foreach item=task key=taskPath from=$tasks}
        
        {*
        {$taskGroup = strtok($taskPath, '/')}
        {if $taskGroup != $lastTaskGroup}
            <h2>{$taskGroup}/</h2>
        {/if}
        {$lastTaskGroup = $taskGroup}
        *}

        <div class="panel panel-default">
            <div class="panel-heading">
                    <span class="glyphicon glyphicon-{$task.icon}" aria-hidden="true"></span>
                    {$task.title|escape}
                    <small class="pull-right">{$taskPath}</small>
            </div>
    
            <div class="panel-body">
                {$task.description|escape|markdown}
        
                {if $task.warning}
                    <div class="alert alert-warning" role="alert">{$task.warning|escape|markdown}</div>
                {/if}
        
                <p><a class="btn btn-{tif $task.warning ? warning : default}" href="/site-admin/tasks/{$taskPath}" role="button">{$task.title|escape} &raquo;</a></p>
            </div>
        </div>
    {foreachelse}
        <div class="alert alert-info">No tasks are available to execute</div>
    {/foreach}
{/block}