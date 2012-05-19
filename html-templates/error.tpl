{extends designs/site.tpl}

{block "content"}
	<section class="error">
		<h1>Uh oh&hellip;</h1>
		<p>{$message|default:"An error has occurred"}</p>
	</section>
{/block}