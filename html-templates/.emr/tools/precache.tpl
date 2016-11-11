{extends "design.tpl"}

{block content}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li class="active">Precache</li>
    </ol>
    {if $message}
    <div class="alert alert-info" role="alert">{$message|escape}</div>
    {/if}
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Select directory trees to precache</h4></div>
            <form method="POST">
                <fieldset class="form-group">
                    <ul>
                        {foreach item=Collection from=SiteCollection::getAllRootCollections(true)}
                            {*<li><label><input type="checkbox" name="collections[]" value="{$Collection->Handle|escape}" checked> {$Collection->Handle|escape}</label></li>*}
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="collections[]" value="{$Collection->Handle|escape}">
                                    {$Collection->Handle|escape}
                                </label>
                            </div>
                        {/foreach}
                    </ul>
                    <input type="submit" class="btn btn-default" value="Precache selected collections">
                </fieldset>
                
            </form>
        </div>
    </div>
{/block}