{extends "sql.tpl"}

{block "content"}

	{if $success}
		<p class="notify success">Query executed successfully.</p>
	{else}
		<p class="notify error">Query failed. MySQL said: {$error|escape}</p>
	{/if}
	
	{$dwoo.parent}
{/block}