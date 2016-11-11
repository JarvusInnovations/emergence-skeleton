{extends "design.tpl"}

{block "content"}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li class="active">Clear APC Cache</li>
    </ol>

    {if $clear || $userClear || $systemClear}
    <div class="alert alert-info" role="alert">{if $clear}APC cache cleared.<br>{/if}{if $userClear}Cleared user cache.<br>{/if}{if $systemClear}Cleared system cache.<br>{/if}</div>
    {/if}

    <div class="container">
        <div class="row">
            <form method="POST">
                <h2>Clear APC</h2>
                <input type="submit" name="target" value="System">
            	<input type="submit" name="target" value="User">
            	<input type="submit" name="target" value="All">
            </form>
        </div>
    </div>
{/block}