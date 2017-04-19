{extends "designs/site.tpl"}

{block "title"}Masquerade &mdash; {$dwoo.parent}{/block}

{block "content"}
    <form method="POST">
        <fieldset class="shrink">
            {field inputName=username label=Username required=true attribs='autofocus autocapitalize="none" autocorrect="off" spellcheck="false"' hint='Enter another user\'s username or email to switch into their account'}

            <div class="submit-area">
                <input type="submit" class="button submit" value="Log In">
                {if RegistrationRequestHandler::$enableRegistration}
                    <span class="submit-text">or <a href="/register{tif $.request.return || $.server.SCRIPT_NAME != '/login' ? cat('?return=', escape(default($.request.return, $.server.REQUEST_URI), url))}">Register</a></span>
                {/if}
            </div>
        </fieldset>
	</form>
{/block}