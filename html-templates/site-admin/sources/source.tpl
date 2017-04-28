{extends "design.tpl"}

{block title}{$source->getId()} &mdash; {$dwoo.parent}{/block}

{block nav}
    {$activeSection = 'sources'}
    {$dwoo.parent}
{/block}

{block breadcrumbs}
    <li><a href="/site-admin/sources">Sources</a></li>
    <li class="active"><a href="/site-admin/sources/{$source->getId()}">{$source->getId()}</a></li>
{/block}

{block css}
    {$dwoo.parent}
    <style>
        .worktree-status {
            font-family: monospace;
        }
        .worktree-file label {
            display: block;
        }
        .worktree-file.added {
            background-color: #dbffdb;
        }
        .worktree-file.modified {
            background-color: #fff3b2;
        }
        .worktree-file.deleted {
            background-color: #ffdddd;
        }
        .worktree-file.untracked {
            background-color: #dddddd;
        }
    </style>
{/block}

{block "js-bottom"}
    {$dwoo.parent}
    {jsmin "site-admin/source.js"}
{/block}

{block "content"}
    {load_templates "templates.tpl"}
    {$status = $source->getStatus()}

    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <div class="btn-group">
                {if !$source->isInitialized()}
                    <a href="/site-admin/sources/{$source->getId()}/initialize" class='btn btn-primary'>
                        <i class="glyphicon glyphicon-play-circle"></i>
                        Initialize Repository
                    </a>
                {/if}
            </div>
        </div>

        <h1>{$source->getId()}</h1>
    </div>


    {template fileStatus file group source}
        {strip}
            {$status = tif($group == staged ? $file.indexStatus : $file.workTreeStatus)}
            <li class="worktree-file {tif $file.staged ? staged} {tif $file.unstaged ? unstaged} {tif $file.tracked ? tracked : untracked} {tif $file.ignored ? ignored} {tif $status == 'A' ? added} {tif $status == 'M' || $status == 'R' ? modified} {tif $status == 'D' ? deleted}">
                <a class="pull-right" href="/site-admin/sources/{$source->getId()}/diff/{$group}/{$file.path|escape}">diff</a>
                <label>
                    <input type="checkbox" name="paths[]" value="{$file.path|escape}">
                    &nbsp;
                    <span class="status">{$status|default:'&nbsp;'}</span>
                    &emsp;
                    <span class="path">{$file.path|escape}</span>
                    {if $file.renamedPath}
                        &emsp;&rarr;&emsp;
                        <span class="renamed-path">{$file.renamedPath|escape}</span>
                    {/if}
                </label>
            </li>
        {/strip}
    {/template}

    {if $source->isInitialized()}
        {$workTreeStatus = $source->getWorkTreeStatus(array(groupByStatus=yes))}

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="btn-group btn-group-xs pull-right">
                    <a class="btn btn-default" href="/site-admin/sources/{$source->getId()}/diff/staged">Diff</a>
                </div>
                <h2 class="panel-title">Staged Commit</h2>
            </div>

            <div class="panel-body">
                {if !$workTreeStatus.staged}
                    <div class="alert alert-info" role="alert">Stage some changes from the git working tree below to start building a commit</div>
                {else}
                    <form class="checkbox" method="POST" action="/site-admin/sources/{$source->getId()|escape}/unstage">
                        <ul class="list-unstyled worktree-status worktree-staged">
                            {foreach item=file from=$workTreeStatus.staged}
                                {fileStatus $file group=staged source=$source}
                            {/foreach}
                        </ul>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-minus"></span> Untage Selected
                            </button>
                            <button type="button" class="btn btn-default worktree-select-all">
                                <span class="glyphicon glyphicon-check"></span> Select All
                            </button>
                            <button type="button" class="btn btn-default worktree-select-none">
                                <span class="glyphicon glyphicon-unchecked"></span> Select None
                            </button>
                        </div>
                    </form>

                    <hr>

                    <form class="form-horizontal" method="POST" action="/site-admin/sources/{$source->getId()|escape}/commit">
                        <div class="form-group">
                            <label for="inputCommitAuthor" class="col-sm-2 control-label">Author</label>
                            <div class="col-sm-10">
                                <input class="form-control" id="inputCommitAuthor" name="author" value="{$.User->FullName|escape} <{$.User->Email|escape}>">
                            </div>
                        </div>

                        {$draftCommitMessage = explode("\n\n", $source->getDraftCommitMessage(), 2)}
                        <div class="form-group">
                            <label for="inputCommitSubject" class="col-sm-2 control-label">Subject</label>
                            <div class="col-sm-10">
                                <input class="form-control" id="inputCommitSubject" name="subject" placeholder="Update &hellip;" value="{$draftCommitMessage[0]|escape}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputCommitExtended" class="col-sm-2 control-label">Extended Description</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" rows="3" id="inputCommitExtended" name="extended" placeholder="Add an optional extended description&hellip;">{$draftCommitMessage[1]|escape}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-primary">Commit</button>
                                <button type="submit" class="btn btn-default" name="action" value="save-draft">Save Message Draft</button>
                            </div>
                        </div>
                    </form>
                {/if}
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="btn-group btn-group-xs pull-right">
                    <a class="btn btn-default" href="/site-admin/sources/{$source->getId()}/diff/unstaged">Diff</a>
                    <a class="btn btn-default" href="/site-admin/sources/{$source->getId()}/clean">Clean</a>
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Sync <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                        <li><a href="/site-admin/sources/{$source->getId()}/sync-from-vfs">Update <strong>git working tree</strong> <div class="small">from emergence VFS</div></a></li>
                        <li><a href="/site-admin/sources/{$source->getId()}/sync-to-vfs">Update <strong>emergence VFS</strong> <div class="small">from git working tree</div></a></li>
                    </ul>
                </div>
                <h2 class="panel-title">Git Working Tree</h2>
            </div>

            <div class="panel-body checkbox">
                {if $workTreeStatus.unstaged}
                    <form method="POST" action="/site-admin/sources/{$source->getId()|escape}/stage">
                        <h3>Unstaged</h3>
                        <ul class="list-unstyled worktree-status worktree-unstaged">
                            {foreach item=file from=$workTreeStatus.unstaged}
                                {fileStatus $file group=unstaged source=$source}
                            {/foreach}
                        </ul>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span> Stage Selected
                            </button>
                            <button type="button" class="btn btn-default worktree-select-all">
                                <span class="glyphicon glyphicon-check"></span> Select All
                            </button>
                            <button type="button" class="btn btn-default worktree-select-none">
                                <span class="glyphicon glyphicon-unchecked"></span> Select None
                            </button>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-success"><strong>The working tree is clean.</strong> If you've made changes in the emergence VFS, <a href="/site-admin/sources/{$source->getId()}/sync-from-vfs">update the git working tree from emergence VFS</a>.</div>
                {/if}
            </div>
        </div>

        {$upstreamDiff = $source->getUpstreamDiff()}
        <div class="panel panel-{tif $upstreamDiff.error ? danger : default}">
            <div class="panel-heading">
                <small class="pull-right">{$source->getWorkingBranch()|escape}&harr;{$source->getUpstreamBranch()|escape}</small>
                <h2 class="panel-title">Branch Status</h2>
            </div>

            {if $upstreamDiff.error}
                <pre class="panel-body" role="alert">{$upstreamDiff.error|escape}</pre>
            {else}
                <table class="table panel-body">
                    <thead>
                        <tr>
                            <th width="50%">
                                <form method="POST" action="/site-admin/sources/{$source->getId()}/push" onsubmit="return confirm('Are you sure?')">
                                    {$upstreamDiff.ahead|number_format} commit{tif $upstreamDiff != 1 ? s} ahead
                                    {if $upstreamDiff.ahead && !$upstreamDiff.behind}
                                        <button type="submit" class="btn btn-default btn-xs">Push</button>
                                    {/if}
                                </form>
                            </th>
                            <th width="50%">
                                <form method="POST" action="/site-admin/sources/{$source->getId()}/pull" onsubmit="return confirm('Are you sure?')">
                                    {$upstreamDiff.behind|number_format} commit{tif $upstreamDiff != 1 ? s} behind
                                    {if $upstreamDiff.behind && !$upstreamDiff.ahead}
                                        <button type="submit" class="btn btn-default btn-xs">Pull (fast fwd)</button>
                                    {/if}
                                </form>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach item=commit from=$upstreamDiff.commits}
                            <tr>
                                {if $commit.position == behind}
                                    <td width="50%"></td>
                                {/if}
                                <td width="50%">
                                    <small class="label label-{tif $commit.position == ahead ? success : info}">{$commit.hash|substr:0:8}</small> {$commit.subject|escape}
                                    <div class="small">
                                        by <a href="mailto:{$commit.authorEmail|escape}">{$commit.authorName|escape}</a>
                                        on {$commit.timestamp|date_format:"%b %e, %Y %l:%M%P"}
                                    </div>
                                </td>
                                {if $commit.position == ahead}
                                    <td width="50%"></td>
                                {/if}
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
    {/if}

    <div class="panel panel-default">
        <div class="panel-heading">
            {if $source->isInitialized()}
                <small class="pull-right"><a href="{$source->getCloneUrl()|escape}">{$source->getCloneUrl()|escape}</a></small>
            {/if}
            <h2 class="panel-title">Repository Configuration</h2>
        </div>

        <dl class="panel-body dl-horizontal">
            <dt>status</dt>
            <dd><span class="label label-{sourceStatusCls $status}">{$status}</span></dd>

            {if $source->isInitialized()}
                <dt>clone url</dt>
                <dd><a href="{$source->getCloneUrl()|escape}">{$source->getCloneUrl()|escape}</a></dd>
            {/if}

            <dt>working branch</dt>
            <dd>{$source->getWorkingBranch()}</dd>

            <dt>upstream branch</dt>
            <dd>{$source->getUpstreamBranch()}</dd>

            <dt>remote</dt>
            <dd>{$source->getRemoteUrl()}</dd>

            {if $source->getRemoteProtocol() == 'ssh'}
                <dt>ssh deploy key</dt>
                <dd>
                    {$deployKey = $source->getDeployKey()}
                    {if $deployKey}
                        Fingerprint: {$deployKey->getFingerprint()}
                    {else}
                        <em>None configured</em>
                    {/if}
                    <a class="btn btn-default btn-xs" href="/site-admin/sources/{$source->getId()}/deploy-key">Manage Deploy Key</a>
                </dd>
            {/if}

            {$trees = $source->getTrees()}
            <dt>mappings</dt>
            <dd>
                <details>
                    <summary>{$trees|count|number_format} mapping{tif count($trees) != 1 ? s}</summary>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Repository Path</th>
                                <th>Site Path</th>
                                <th>Local Only</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=tree from=$trees}
                                <tr>
                                    <td>{$tree.gitPath|escape}</td>
                                    <td>{$tree.vfsPath|escape}</td>
                                    <td>{tif $tree.localOnly ? 'yes' : 'no'}</td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </details>
            </dd>
        </dl>
    </div>
{/block}