{extends "design.tpl"}

{block "content"}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li class="active">Table Manager</li>
    </ol>
    
    <div class="panel panel-default">
        <div class="panel-heading">Select Model</div>
        <form>
    		<table class="table row-stripes row-highlight">
    			<tr>
    				<td>
                        <fieldset class="form-group">
        					<select name="class" size="30" style="height: 300px; width: 600px;">
        						{foreach item=class from=$classes}<option>{$class}</option>{/foreach}
        					</select>
                        </fieldset>
    				</td>
    				<td valign="top" align="left" width="200">
    					<input type="submit" class="btn btn-default" value="Generate CREATE TABLE SQL" onclick="this.form.action='/.emr/tools/table-manager/sql'">
    					<input type="submit" class="btn btn-default" value="Generate Ext Model" onclick="this.form.action='/.emr/tools/table-manager/ext-model'">
    					{*<input type="submit" value="Generate Ext Columns" onclick="this.form.action='/.emr/tools/table-manager/ext-columns'">
    					<input type="submit" value="Repair nested-set" onclick="this.form.action='/.emr/tools/table-manager/renest'">*}
    				</td>
    			</tr>
    		</table>
        </form>
    </div>

{/block}