{extends "sql.tpl"}

{block "content"}

	{if $success}
		<p class="status">Query executed successfully</p>
	{else}
		<p class="status invalid">Query failed. MySQL said: {$error|escape}</p>
	{/if}
	
	{$dwoo.parent}
{/block}