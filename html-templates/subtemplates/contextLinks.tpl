{template contextLink Context prefix='' suffix='' class=''}{strip}

{if !$Context}
    <em>[context deleted]</em>
{elseif is_a($Context, 'Person')}
	<a href="/people/{$Context->Handle}" class="{$class}">{$prefix}{$Context->FullNamePossessive|escape} Profile{$suffix}</a>
{elseif is_a($Context, 'Media')}
	<a href="{$Context->getThumbnailRequest(1000,1000)}" class="attached-media-link {$class}" title="{$Context->Caption|escape}">
		{$prefix}
		<img src="{$Context->getThumbnailRequest(25,25)}" alt="{$Context->Caption|escape}">
		&nbsp;{$Context->Caption|escape}
		{$suffix}
	</a>
{else}
	<a href="/{Router::getClassPath($Context)}/{tif $Context->Handle ? $Context->Handle : $Context->ID}" class="{$class}">{$Context->Title|escape}</a>
{/if}

{/strip}{/template}