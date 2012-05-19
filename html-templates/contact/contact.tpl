{extends designs/site.tpl}

{block "title"}Contact &mdash; {$dwoo.parent}{/block}

{block "content"}

	<form action="/contact" method="POST">
		<fieldset>

			<h2>Contact Us</h2>
            
            <div class="field">
                <label for="name">Name:</label>
			    <input type="text" id="name" name="Name" placeholder="Your name" class="{tif $validationErrors.Name ? 'error'}" value="{refill field=Name}">
				{if $validationErrors.Name}
					<p class="error"> * {$validationErrors.Name} </p>
				{/if}
            </div>

			<div class="field">
                <label for="email">Email:</label>
			    <input type="email" id="email" name="Email" placeholder="Your email address"/ class="{tif $validationErrors.Email ? 'error'}" value="{refill field=Email}">
				{if $validationErrors.Email}
					<p class="error"> * {$validationErrors.Email} </p>
				{/if}
			</div>

			<div class="field">
			    <label class="phone" for="phone">Phone &#35;:</label>
			    <input type="tel" id="phone" name="Phone" placeholder="2151112222"/ class="{tif $validationErrors.Phone ? 'errors'}" value="{refill field=Phone}">
				{if $validationErrors.Phone}
					<p class="error"> * {$validationErrors.Phone} </p>
				{/if}
            </div>
			
			<div class="field">
			    <label class="textarea" for="message">Message:</label>
		    	<textarea id="message" name="Message" placeholder="Your questions or comments" class="{tif $validationErrors.Phone ? 'errors'}">{refill field=Message}</textarea>
				{if $validationErrors.Message}
					<p class="error"> * {$validationErrors.Message} </p>
				{/if}
		    </div>
            
		</fieldset>

		<input type="submit" value="Submit" id="submit"/>
	</form>

{/block}