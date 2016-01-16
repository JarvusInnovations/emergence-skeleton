{extends "designs/site.tpl"}

{block "css"}
    {$dwoo.parent}
	<link rel="stylesheet" type="text/css" href="/css/forms.css">
	<style>
		table td input { margin: 2px; }
        
        select { height: 500px; width: 400px; } 
	</style>
{/block}

{block "content"}
	<form>
		<table>
			<tr>
				<th scope="col">Select Model</th>
                <th scope="col">Model Extenders</th>
				<th scope="col">Database Actions</th>
			</tr>
			<tr>
				<td>
					<select name="class" size="30">
						{foreach item=class from=$classes}<option>{$class}</option>{/foreach}
					</select>
				</td>
                <td valign="top" align="left" width="200">
                    <input type="submit" value="Generate Ext Model" onclick="this.form.action='/site-admin/table-manager/ext-model'">
    				<input type="submit" value="Generate Ext Columns" onclick="this.form.action='/site-admin/table-manager/ext-columns'">
                </td>
				<td valign="top" align="left" width="200">
					<input type="submit" value="Generate CREATE TABLE SQL" onclick="this.form.action='/site-admin/table-manager/sql'">
                    <input type="submit" value="Generate ALTER TABLE SQL" onclick="this.form.action='/site-admin/table-manager/alter-table'">
					<input type="submit" value="Repair nested-set" onclick="this.form.action='/site-admin/table-manager/renest'">
				</td>
			</tr>
		</table>
	</form>

{/block}