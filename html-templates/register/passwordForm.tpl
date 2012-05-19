{extends "designs/site.tpl"}

{block "app-class"}login{/block}

{block "app-menu"}{/block}

{block "css"}
	{$dwoo.parent}
	<link rel="stylesheet" type="text/css" href="/css/forms.css">
{/block}

{block "content"}

<div id="app-body">
	<h1>Create new password</h1>
	
	{if $error}
		<p class="error">{$error|escape}</p>
	{/if}
	
	<form method="POST" id="password-form" class="generic">
		
		<div class="field">
			<label>Password</label>
			<input class="text" type="password" name="Password" id="password">
		</div>

		<div class="field">
			<label><span>Re-type</span> Password</label>
			<input class="text" type="password" name="PasswordConfirm" id="password2">
		</div>
		
		<div class="submit">	
			<input type="submit" class="submit" value="Change password" class="sideSubmit">
		</div>
	</form>
</div>

{/block}