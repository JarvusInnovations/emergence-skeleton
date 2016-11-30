{extends "design.tpl"}

{block "content"}    
    <div class="page-header">
        <h2>Emergence Git Tool</h2>
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
    			<td valign="top"><a href="/.emr/git/{$Repo->ID}">{$Repo->ID}</td>
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