{extends "designs/site.tpl"}

{block "content"}
	<h1>Please confirm</h1>
	<p class="confirm">{$question}</p>
	<form method="POST">
		<input type="submit" name="Sure" value="Yes">
		<input type="button" name="Sure" value="No" onclick="javascript:history.go(-1);">
	</form>
{/block}