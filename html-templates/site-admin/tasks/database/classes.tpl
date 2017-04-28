{extends "task.tpl"}

{block content}
    <div class="panel panel-default">
        <div class="panel-heading">Select ActiveRecord class</div>

        <div class="panel-body">
            <ul>
                {foreach item=class from=$classes}
                    <li><a href="?class={$class|escape:url}">{$class|escape}</li>
                {/foreach}
            </ul>
        </div>
    </div>
{/block}