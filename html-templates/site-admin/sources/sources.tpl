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
        <tr>
        	<th>Repository ID</th>
    		<th>Working Branch</th>
    		<th>Upstream Branch</th>
    		<th>Status</th>
    	</tr>

    	{foreach item=Repo from=$Repos}
    		<tr>
    			<td valign="top"><a href="/site-admin/git/{$Repo->ID}">{$Repo->ID}</td>
    			<td valign="top">{$Repo->WorkingBranch}</td>
    			<td valign="top">{$Repo->UpstreamBranch}</td>
    			<td valign="top">
    				{if $Repo->isRepositoryInitialized()}
    					<pre>{$Repo->Status}</pre>
                    {else}
                        <pre>Not Initialized</pre>
    				{/if}
    			</td>
    		</tr>
    	{/foreach}
    </table>
{/block}