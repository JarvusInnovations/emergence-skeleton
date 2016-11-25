{extends "design.tpl"}

{block "content"}
    <form method="POST" class="panel panel-warning">
        <div class="panel-heading"><h1 class="panel-title">Confirmation required</h1></div>

        <div class="panel-body">
            {$question|escape|default:"Are you sure you want to continue?"|markdown}
        </div>

        <div class="panel-footer">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default" onclick="javascript:history.go(-1);">No</button>
                <button type="submit" class="btn btn-primary">Yes</button>
            </div>
        </div>
    </form>
{/block}