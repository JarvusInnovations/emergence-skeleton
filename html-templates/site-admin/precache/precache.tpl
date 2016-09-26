{extends designs/site.tpl}

{block title}Precache trees &mdash; {$dwoo.parent}{/block}

{block content}
    <script>
    function selectAllCollections() {
        var checkboxNodes = document.querySelectorAll('input[type=checkbox][name="collections[]"]'),
            i = 0, length = checkboxNodes.length;

        for (; i < length; i++) {
            checkboxNodes[i].checked = true;
        }
    }
    </script>

    <form method="POST">
        <h2>Select trees to precache</h2>

        {if $message}
            <pre>{$message|escape}</pre>
        {/if}

        <ul>
            {foreach item=Collection from=SiteCollection::getAllRootCollections(true)}
                <li><label><input type="checkbox" name="collections[]" value="{$Collection->Handle|escape}"> {$Collection->Handle|escape}</label></li>
            {/foreach}
            <li><input type="text" placeholder="path/to/tree" name="collections[]"></li>
        </ul>
        
        <input type="submit" value="Precache selected collections">
        <input type="button" value="Select all" onclick="selectAllCollections()">
    </form>
{/block}