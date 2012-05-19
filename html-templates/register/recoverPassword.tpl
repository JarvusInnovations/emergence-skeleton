{extends "designs/site.tpl"}

{block "app-class"}{/block}




{block "app-menu"}{/block}


{block "content"}
<div id="app-body">
		
	{if $error}
		<p class="error">{$error|escape}</p>
	{/if}
	
	<form method="POST" id="recover-form" class="generic single">
	
		<h1>Recover your password</h1>
		
		<fieldset class="section">
		<div class="field">
			<label>Email or Username</label>
			<input class="text" type="text" name="username" id="username" value="{refill field=username}">
		</div>
		
		<div class="submit">
			<input type="submit" value="Reset my password" class="submit">
		</div>
		</fieldset>
		
	</form>
	
	
</div>
{/block}