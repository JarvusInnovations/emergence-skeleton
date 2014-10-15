{extends "designs/site.tpl"}

{block "content"}
	<form>
		<table>
			<tr>
				<th scope="col">Select Model</th>
				<th scope="col">Actions</th>
			</tr>
			<tr>
				<td>
					<select name="class" size="30" style="height: 300px;">
						{foreach item=class from=$classes}<option>{$class}</option>{/foreach}
					</select>
				</td>
				<td valign="top" align="center" width="200">
					<input type="submit" value="Generate CREATE TABLE SQL" onclick="this.form.action='/site-admin/table-manager/sql'">
					<input type="submit" value="Generate Ext Model" onclick="this.form.action='/site-admin/table-manager/ext-model'">
					<input type="submit" value="Generate Ext Columns" onclick="this.form.action='/site-admin/table-manager/ext-columns'">
					<input type="submit" value="Repair nested-set" onclick="this.form.action='/site-admin/table-manager/renest'">
				</td>
			</tr>
		</table>
	</form>

{/block}