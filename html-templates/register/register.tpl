{extends "designs/site.tpl"}

{block "title"}Register &mdash; {$dwoo.parent}{/block}

{block "content"}
	{$User = $data}
	
	<form method="POST" id="register">
	{strip}
		{if $User->validationErrors}
			<h2>There were problems with your submission:</h2>
			<ul class="errors">
			{foreach item=error key=field from=$User->validationErrors}
				<li>{$field}: {$error|escape}</li>
			{/foreach}
			</ul>
		{/if}
	
		<fieldset>
			<div class="field-pair">
				<div class="field text" id="register-firstname">
					<label>
						<span>First Name</span>
						<input type="text" name="FirstName" value="{refill field=FirstName}">
					</label>
				</div>
			
				<div class="field text" id="register-lastname">
					<label>
						<span>Last Name</span>
						<input type="text" name="LastName" value="{refill field=LastName}">
					</label>
				</div>
			</div>
			
			<div class="field text email" id="register-email">
				<label>	
					<span>Email Address</span>
					<input type="email" name="Email" value="{refill field=Email}">
				</label>
				<div class="hint" id="register-privacy">
					This will not be shared with anyone.
				</div>
			</div>
			
			<div class="field text" id="register-username">
				<label>
					<span>Username</span>
					<input type="text" name="Username" value="{refill field=Username}">
				</label>
			</div>
			
			<div class="field-pair">
				<div class="field text password" id="register-password">
					<label>
						<span>Password</span>
						<input type="password" name="Password">
					</label>
				</div>
				
				<div class="field text password" id="register-passwordconfirm">
					<label>
						<span>(Confirm)</span>
						<input type="password" name="PasswordConfirm">
					</label>
				</div>
			</div>
			
			<div class="submit">
				<input type="submit" class="button submit" value="Create account">
			</div>
		</fieldset>
	{/strip}
	</form>
	
	<p class="form-hint">Already have an account? <a href="/login">Log in</a></p>
{/block}