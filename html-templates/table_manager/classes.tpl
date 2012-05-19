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
					<input type="submit" value="Generate CREATE TABLE SQL" onclick="this.form.action='/{Site::$requestPath[0]}/sql'">
					<input type="submit" value="Generate ExtJS Metadata" onclick="this.form.action='/{Site::$requestPath[0]}/metadata'">
					<input type="submit" value="Generate ExtJS Column Model" onclick="this.form.action='/{Site::$requestPath[0]}/column_model'">
					<input type="submit" value="Repair nested-set" onclick="this.form.action='/{Site::$requestPath[0]}/renest'">
				</td>
			</tr>
		</table>
	</form>

{/block}