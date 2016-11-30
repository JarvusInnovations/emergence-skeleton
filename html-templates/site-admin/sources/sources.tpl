{extends "design.tpl"}

{block nav}
    {$activeSection = 'sources'}
    {$dwoo.parent}
{/block}

{block "content"}
    <div class="page-header">
        <h1>Site Sources</h1>
    </div>

    <table class="table">
        <thead>
            <tr>
            	<th>Repository ID</th>
        		<th>Working Branch</th>
        		<th>Upstream Branch</th>
        		<th>Status</th>
        	</tr>
        </thead>

        <tbody>
        	{foreach item=source key=id from=$sources}
        		<tr>
        			<td valign="top"><a href="/site-admin/sources/{$id|escape:url}">{$id|escape}</td>
        			<td valign="top">{$source->getWorkingBranch()}</td>
        			<td valign="top">{$source->getUpstreamBranch()}</td>
        			<td valign="top">
        				{if $source->getRepository()}
        					<pre>{$source->getRepository()->getStatus()}</pre>
                        {else}
                            <em>Not Initialized</em>
        				{/if}
        			</td>
        		</tr>
        	{/foreach}
        </tbody>
    </table>
{/block}