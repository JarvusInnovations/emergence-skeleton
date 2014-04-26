{extends "designs/site.tpl"}

{block "title"}Masquerade &mdash; {$dwoo.parent}{/block}

{block "content"}
	<form method="POST">
		{strip}
			<fieldset>
				<div class="field text">
					<label>
						<span>Username</span>
						<input type="text" name="username" value="{refill field=username}" autocorrect="off" autocapitalize="off">
					</label>
					<div class="hint">
						Enter another user's username or email to switch into their account
					</div>
				</div>
			
				<div class="submit">
					<input type="submit" class="button submit" value="Switch to User">		
				</div>
			</fieldset>
		{/strip}
	</form>
{/block}