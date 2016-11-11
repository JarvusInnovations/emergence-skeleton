{extends "design.tpl"}

{block "content"}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li><a href="/.emr/tools/table-manager">Table Manager</a></li>
        <li class="active">SQL</li>
    </ol>
    {if $responseID=='sqlExecuted'}
        {if $success}
        	<p class="alert alert-success" role="alert">Query executed successfully.</p>
    	{else}
    		<p class="alert alert-danger" role="alert">Query failed. MySQL said: {$error|escape}</p>
    	{/if}
    {/if}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4><span style="font-family: monospace">CREATE TABLE</span> SQL for {$class|escape}</h4>
        </div>
        <form method="POST" class="generic">
    		<textarea name="sql" id="sql" onfocus="this.select()" style="width: 100%;height: 500px;">{$query|escape}</textarea>
            <input type="submit" class="btn btn-default" value="Execute SQL &raquo;">
	    </form>
    </div>
{/block}