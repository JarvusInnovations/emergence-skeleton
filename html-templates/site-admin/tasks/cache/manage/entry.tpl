{extends "task.tpl"}

{block content}

    <div class="panel panel-default">
        <div class="panel-heading">Application Cache: <code>{$entryKey|escape}</code></div>

        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Key</dt>
                <dd><code>{$entryKey|escape}</code></dd>

                <dt>Hits</dt>
                <dd>{$entry.hits|number_format}</dd>

                <dt>Size</dt>
                <dd>{bytes $entry.size}</dd>

                <dt>Accessed</dt>
                <dd><time datetime="{html_time $entry.accessTime}">{fuzzy_time $entry.accessTime}</time></dd>

                <dt>Created</dt>
                <dd><time datetime="{html_time $entry.createTime}">{fuzzy_time $entry.createTime}</time></dd>

                <dt>Modified</dt>
                <dd><time datetime="{html_time $entry.modifyTime}">{fuzzy_time $entry.modifyTime}</time></dd>

                <dt>Value</dt>
                <dd><pre>{$entry.value|var_export:true|escape}</pre></dd>
            </dl>
        </div>
    </div>

{/block}