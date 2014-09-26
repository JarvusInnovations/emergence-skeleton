{extends designs/site.tpl}

{block title}Precache trees &mdash; {$dwoo.parent}{/block}

{block content}
    <form method="POST">
        <h2>Select trees to precache</h2>

        {if $message}
            <pre>{$message|escape}</pre>
        {/if}

        <ul>
            {foreach item=Collection from=SiteCollection::getAllRootCollections(true)}
                <li><label><input type="checkbox" name="collections[]" value="{$Collection->Handle|escape}" checked> {$Collection->Handle|escape}</label></li>
            {/foreach}
        </ul>
        
        <input type="submit" value="Precache selected collections">
    </form>
{/block}