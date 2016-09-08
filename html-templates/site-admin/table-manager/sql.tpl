{extends "designs/site.tpl"}

{block "css"}
	{$dwoo.parent}
	<link rel="stylesheet" type="text/css" href="/css/forms.css">
	<style>
		#sql {
			width: 98%;
			height: 30em;
			margin: 0 auto;
		}
		div.submit {
			text-align: right;
		}
		input.submit {
			margin: 0 1em 1em 0 !important;
			width: 250px;
		}
	</style>
{/block}

{block "content"}

	<h2><span style="font-family: monospace">CREATE TABLE</span> SQL for {$class|escape}</h2>
	<form method="POST" class="generic">
		<div class="submit">
			<input type="button" class="submit" value="&laquo; Return to class list" onclick="location='/table_manager';">
			<input type="submit" class="submit" value="Execute SQL &raquo;">
		</div>
		<textarea name="sql" id="sql" onfocus="this.select()">{$query|escape}</textarea>
	</form>
{/block}