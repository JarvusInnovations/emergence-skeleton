{extends "design.tpl"}

{block "content"}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li><a href="/.emr/tools/table-manager">Table Manager</a></li>
        <li class="active">Ext Modal</li>
    </ol>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>ExtJS Model for {$class|escape}</h4>
        </div>
        
	    <textarea style="width: 100%; height: 30em; margin: 0 auto; font-family: monospace" onfocus="this.select()">{$data|escape}</textarea>
    </div>
{/block}