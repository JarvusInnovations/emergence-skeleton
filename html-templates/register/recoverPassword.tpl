{extends "designs/site.tpl"}

{block "title"}Reset Password &mdash; {$dwoo.parent}{/block}

{block "content"}
    <header class="page-header">
		<h2 class="header-title">Reset Your Password</h2>
	</header>

	<p class="page-info">Enter the username or email address associated with your account below, and you will receive an email with instructions to reset your password.</p>

	{if $error}
		<div class="notify error">{$error|escape}</div>
	{/if}
	
	<form method="POST" id="recover-form" class="generic single">
		<fieldset class="shrink">
		    {field inputName='username' label='Email or Username' required=true attribs='autofocus'}
            <div class="submit-area">
                <input type="submit" class="button submit" value="Reset Password">
            </div>
		</fieldset>
	</form>
{/block}