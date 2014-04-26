{extends "designs/site.tpl"}

{block title}{if $.request.q}{$.request.q|escape} &mdash;{/if} Search &mdash; {$dwoo.parent}{/block}

{block "breadcrumbs"}
    <a href="/search">Search</a>
	{if $.request.q}
		<a href="/search?q={$.request.q|escape}">{$.request.q|escape}</a>
	{/if}
{/block}


{block content}
	
	{if !array_filter($data)}
		<p>Your search found no results.</p>
	{else}	
		<ul class="search-results">
    		{foreach key=className item=results from=array_filter($data)}
    			{$count = count($results)}
    			<section class="results-group">
    				<h1 id="results-{$className}">{$count|number_format} {Inflector::pluralizeRecord($className, $count)}</h1>
    				{foreach item=result from=$results}
    					<li class="search-result">{contextLink $result}</li>
    				{/foreach}
    			</section>
    		{/foreach}
		</ul>
	{/if}

{/block}