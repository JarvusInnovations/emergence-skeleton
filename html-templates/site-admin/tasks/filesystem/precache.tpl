{extends "task.tpl"}

{block content}
    <script>
    function selectAllCollections() {
        $('input[type=checkbox][name="collections[]"]').prop('checked', true);
    }
    </script>

    {if $message}
        <pre class="alert alert-info" role="alert">{$message|escape}</pre>
    {/if}

    <form method="POST" class="panel panel-default">
        <div class="panel-heading">Select trees to precache</div>

        <div class="panel-body">
            <button type="button" onclick="selectAllCollections()" class="btn btn-default btn-sm">Select all</button>

            {foreach item=Collection from=SiteCollection::getAllRootCollections()}
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="collections[]" value="{$Collection->Handle|escape}">
                        {$Collection->Handle|escape}
                    </label>
                </div>
            {/foreach}

            <div class="form-group">
                <input type="text" placeholder="path/to/tree" name="collections[]">
            </div>

            <button type="submit" class="btn btn-primary">Precache selected collections</button>
        </div>
    </form>
{/block}