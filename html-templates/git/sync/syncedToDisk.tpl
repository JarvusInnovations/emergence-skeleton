<h1>Synchronization to disk finished</h1>

<table border="1">
	<tr>
		<th>Path</th>
		<th>Analyzed</th>
		<th>Writted</th>
		<th>Deleted</th>
	</tr>

	{foreach key=path item=result from=$results}
		<tr>
			<td>{$path}</td>
			<td>{$result.analyzed|number_format}</td>
			<td>{$result.written|number_format}</td>
			<td>{$result.deleted|number_format}</td>
		</tr>
	{/foreach}
</table>