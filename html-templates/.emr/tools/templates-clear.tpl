
{extends "design.tpl"}

{block content}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li class="active">Clear Templates Cache</li>
    </ol>
    {if $success}
    <div class="alert alert-info" role="alert">Templates have been cleared</div>
    {/if}
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Clear Templates Cache</h4></div>
            <form method="POST">
                <input type="submit" class="btn btn-default" value="Clear Templates Cache">
            </form>
        </div>
    </div>
{/block}