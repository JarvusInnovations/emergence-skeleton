{extends "designs/site.tpl"}

{block "content"}

	<h2>ExtJS Model for {$class|escape}</h2>
	<textarea style="width: 100%; height: 30em; margin: 0 auto; font-family: monospace" onfocus="this.select()">{$data|escape}</textarea>

{/block}