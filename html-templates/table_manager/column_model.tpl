{extends "designs/site.tpl"}

{block "content"}

	<h2>ExtJS Column Model for {$class|escape}</h2>
	<textarea style="width: 100%; height: 30em; margin: 0 auto;" onfocus="this.select()">{ExtJS::getJson($data)|escape}</textarea>

{/block}