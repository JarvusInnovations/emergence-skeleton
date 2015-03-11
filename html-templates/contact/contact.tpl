{extends designs/site.tpl}

{block "title"}Contact &mdash; {$dwoo.parent}{/block}

{block "content"}
    <header class="page-header">
	    <h2 class="header-title">Contact Us</h2>
	</header>

	<form action="/contact" method="POST" class="contact-form">
		{if $validationErrors}
			<div class="notify error">
				<strong>Please double-check the fields highlighted below.</strong>
			</div>
		{/if}

		<fieldset class="shrink show-required left-labels">
		
			{* field name label='' error='' type=text placeholder='' hint='' required=false attribs='' *}
            
            {field name=Name  label=Name  error=$validationErrors.Name  required=true attribs='autofocus autocapitalize="words"'}
            {field name=Email label=Email error=$validationErrors.Email type=email required=true}
            {field name=Phone label=Phone error=$validationErrors.Phone type=tel hint='Optional. Include your area code.'}

            {textarea name=Message label=Message error=$validationErrors.Message required=true}
            
            <div class="submit-area">
            	<input type="submit" value="Send">
            </div>
            
		</fieldset>
	</form>

{/block}