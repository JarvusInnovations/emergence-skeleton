{extends "task.tpl"}

{block content}

    <div class="panel panel-default">
        <div class="panel-heading">Application Cache Contents</div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Key</th>
                    <th scope="col">Hits</th>
                    <th scope="col">Size</th>
                    <th scope="col">Accessed</th>
                    <th scope="col">Created</th>
                    <th scope="col">Modified</th>
                </tr>
            </thead>
            <tbody>
                {foreach item=entry from=$entries}
                    <tr>
                        <th scope="row">{$entry.key|escape}</th>
                        <td>{$entry.hits|number_format}</td>
                        <td>{bytes $entry.size}</td>
                        <td><time datetime="{html_time $entry.accessTime}">{fuzzy_time $entry.accessTime}</time></td>
                        <td><time datetime="{html_time $entry.createTime}">{fuzzy_time $entry.createTime}</time></td>
                        <td><time datetime="{html_time $entry.modifyTime}">{fuzzy_time $entry.modifyTime}</time></td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

{/block}