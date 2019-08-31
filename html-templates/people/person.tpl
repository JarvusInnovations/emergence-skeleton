{extends "designs/site.tpl"}

{block "title"}{personName $data} &mdash; {$dwoo.parent}{/block}


{block "content"}
	{$Person = $data}

    <header class="page-header">
        <h2 class="header-title">{personName $Person}</h2>
    	{if $Person->Location}
		    <h3 class="header-details"><a href="http://maps.google.com/?q={$Person->Location|escape:url}" target="_blank">{$Person->Location|escape}</a></h3>
		{/if}
        <div class="header-buttons">
            {if $.User->ID == $Person->ID || (ProfileRequestHandler::$accountLevelEditOthers && $.User->hasAccountLevel(ProfileRequestHandler::$accountLevelEditOthers))}
                <a class="button" href="/profile{tif $.User->ID != $Person->ID ? cat('?person=', $Person->ID)}">Edit Profile</a>
            {/if}
        </div>
    </header>
	
	<div id="photos">
		{if $Person->PrimaryPhoto}
			<a href="{$Person->PrimaryPhoto->WebPath}" id="display-photo-link"><img src="{$Person->PrimaryPhoto->getThumbnailRequest(200,200)}" alt="Profile Photo: {personName $Person}" id="display-photo" /></a>
		{else}
			<img src="/thumbnail/person/200x200" alt="Profile Photo: {personName $Person}" id="profilePhoto" />
		{/if}
		<div id="photo-thumbs" class="clearfix">
			{foreach item=Photo from=$Person->Photos}
				<a href="{$Photo->getThumbnailRequest(1024,768)}" class="photo-thumb" id="t{$Photo->ID}" title="{$Photo->Caption|escape}"><img src="{$Photo->getThumbnailRequest(48,48)}" /></a>
			{/foreach}
		</div>
	</div>
	
	<div id="page-intro" class="">
		<h1 class="run-in"></h1>
	</div>

	<div id="info" class="">

		{if $Person->About}
			<h2>About Me</h2>
			<section class="about">
				{$Person->About|escape|markdown}
			</section>
		{/if}
		
		{if $.Session->hasAccountLevel('Staff')}
		<h2>Contact Information (Staff-only)</h2>
		<dl class="section">
			{if $Person->Email}
				<dt>Email</dt>
				<dd><a href="mailto:{$Person->Email}" title="Email {personName $Person}">{$Person->Email}</a></dd>
			{/if}
			
			{if $Person->Phone}
				<dt>Phone</dt>
				<!-- tel: URL scheme fails in desktop browsers -->
				<dd><!-- <a href="tel:{$Person->Phone}"> -->{$Person->Phone|phone}<!-- </a> --></dd>
			{/if}		
		</dl>
		{/if}
	
	</div>
{/block}