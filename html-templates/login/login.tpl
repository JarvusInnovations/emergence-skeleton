{extends "designs/site.tpl"}

{block "title"}Log In &mdash; {$dwoo.parent}{/block}

{block "content"}
	<form method="POST" id="login">

	{foreach item=value key=name from=$postVars}
		{if is_array($value)}
			{foreach item=subvalue key=subkey from=$value}
			<input type="hidden" name="{$name|escape}[{$subkey|escape}]" value="{$subvalue|escape}">
		{else}
			<input type="hidden" name="{$name|escape}" value="{$value|escape}">
		{/if}
	{/foreach}

	<input type="hidden" name="_LOGIN[returnMethod]" value="{refill field=_LOGIN.returnMethod default=$.server.REQUEST_METHOD}">			
	<input type="hidden" name="_LOGIN[return]" value="{refill field=_LOGIN.return default=$.server.REQUEST_URI}">	
	
	{if $authException}
		<p class="error">
			Login Failed: {$authException->getMessage()}
		</p>
	{elseif $error}
		<p class="error">
			Login Failed: {$error}
		</p>
	{/if}	
	
	{strip}
		<fieldset>
			<div class="field text" id="login-username">
				<label>
					<span>Username</span>
					<input type="text" name="_LOGIN[username]" value="{refill field=_LOGIN.username}"
						autocorrect="off" autocapitalize="off">
				</label>
				<div class="hint" id="username-email">
					You can also sign in using your email address.
				</div>
			</div>
		
			<div class="field text password" id="login-password">
				<label>
					<span>Password</span>
					<input type="password" name="_LOGIN[password]">
				</label>
				<div class="hint" id="login-recover">
					<a href="/register/recover">Forgot?</a>
				</div>
			</div>
			
			<div class="submit" id="login-submit">
				<label class="checkbox">
					<input type="checkbox" name="_LOGIN[remember]">
					<span>Remember me</span>
				</label>
				<input type="submit" class="button submit" value="Log in">		
			</div>
		</fieldset>
	{/strip}
	</form>
	
	<p class="form-hint">Don&rsquo;t have an account? <a href="/register">Sign up</a></p>
{/block}