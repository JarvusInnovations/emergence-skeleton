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

{block "content"}
    {load_templates "templates.tpl"}

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

    <div class="panel panel-default">
        <div class="panel-heading"><h2 class="panel-title">Repository Configuration</h2></div>

        <dl class="panel-body dl-horizontal">
            <dt>status</dt>
            <dd><span class="label label-{sourceStatusCls $source}">{$source->getStatus()}</span></dd>

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
                        {$deployKey->getFingerprint()}
                    {/if}
                    <a class="btn btn-default btn-xs" href="/site-admin/sources/{$source->getId()}/deploy-key">Manage Deploy Key</a></dd>
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

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">
                Repository Status: <span class="label label-{sourceStatusCls $source}">{$source->getStatus()}</span>
            </h2>
        </div>

        <div class="panel-body">{dump $source}</div>
    </div>

    <div class="navbar-form navbar-left">
        <h2>
            {$Repo->ID}
            <a href="/site-admin/git/{$Repo->ID}/key" class="btn btn-default">
                <span class="glyphicon glyphicon-lock"></span> Deploy Key
            </a>
        </h2>

    </div>

    <input type="hidden" value="{$Repo->ID}" name="repo" id="js-repo">

    <div class="navbar-form navbar-right emr-git-remoteio">
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group">
                <a class="btn btn-default navbar-btn disabled"><i class="glyphicon glyphicon-transfer"></i> Remote IO</a>

                <input type="submit" id="js-pull" value="Pull (FF)" class="btn btn-default navbar-btn">
                <input type="submit" id="js-push" value="Push (FF)" class="btn btn-default navbar-btn">

            </div>
        </div>
    </div>



    <div class="clearfix"></div>

    <div class="container">

        {if !$Repo->privateKeyExists()}
            <div class="alert alert-info" role="alert">You currently have no key configured. All Git remote functions will proceed without providing authentication credentials.</div>
        {/if}

        <div class="alert hidden" role="alert" id="js-upper-status"></div>

        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Remote</span>
                <input type="text" class="form-control" disabled value="{$Repo->Remote}">
            </div>
        </div>

        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">Upstream Branch</span>
                <input type="text" class="form-control" disabled value="{$Repo->UpstreamBranch}">
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-lg-8"><h2>Local</h2></div>
            <div class="col-lg-4 text-right">
                <button type="button" class="btn btn-default" id="js-clean" alt="Clean Repository">
                    <span class="glyphicon glyphicon-erase"></span> Clean
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" alt="Reset Repository">
                        <span class="glyphicon glyphicon-fire"></span> Reset
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" id="js-reset-remote-head">Remote HEAD</a></li>
                        <li><a href="#">Local HEAD</a></li>
                        {*<li role="separator" class="divider"></li>
                        <li><a href="#">One commit before Local HEAD</a></li>*}
                    </ul>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">Working Branch</span>
                        <input type="text" class="form-control" disabled value="{$Repo->WorkingBranch}">
                    </div>
                </div>
            </div>

            <div class="col-lg-4 emr-git-diskio text-right">
                <div class="btn-group">
                    <a class="btn btn-default disabled"><i class="glyphicon glyphicon-hdd"></i> Disk IO</a>
                    <input type="submit" id="js-to-disk" value="VFS &rarr; Disk" class="btn btn-default">
                    <input type="submit" id="js-from-disk" value="Disk &rarr; VFS" class="btn btn-default">
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        {if $Repo->isRepositoryInitialized()}
            <h3>Raw Status</h3>
			<pre>{$Repo->Status}</pre>
		{else}
			<div class="text-center">
                <h3>Repository not initialized</h3>
                <form method="GET" action="/site-admin/git/{$Repo->ID}/init" >
        			<input type="submit" value="Click here to Initialize" class="btn btn-default">
    			</form>
            </div>
		{/if}
    </div>


    {if $Repo->isRepositoryInitialized()}
        <div id="advancedStatus" class="container">
            <h2>Status</h2>
            <ul class="nav nav-pills">
                <li class="active"><a  href="#status-everything" data-toggle="tab">Everything</a></li>
                <li><a href="#status-staged" data-toggle="tab">Staged</a></li>
                <li><a href="#status-unstaged" data-toggle="tab">Unstaged</a></li>
                <li><a href="#status-tracked" data-toggle="tab">Tracked</a></li>
                <li><a href="#status-untracked" data-toggle="tab">Untracked</a></li>
            </ul>

            <div class="btn-group git-file-buttons">
                <button type="button" class="btn btn-default" id="js-select-all" alt="Select All">
                    <span class="glyphicon glyphicon-check"></span> Select All
                </button>
                <button type="button" class="btn btn-default" id="js-stage-selected" alt="Select All">
                    <span class="glyphicon glyphicon-save-file"></span> Stage Selected
                </button>
            </div>

            <div class="tab-content clearfix">
                <div class="tab-pane active" id="status-everything">
                    {fileBlock $AdvancedStatus.Everything}
                </div>
                <div class="tab-pane" id="status-staged">
                    {fileBlock $AdvancedStatus.Staged}
                </div>
                <div class="tab-pane" id="status-unstaged">
                    {fileBlock $AdvancedStatus.Unstaged}
                </div>
                <div class="tab-pane" id="status-tracked">
                    {fileBlock $AdvancedStatus.Tracked}
                </div>
                <div class="tab-pane" id="status-untracked">
                    {fileBlock $AdvancedStatus.Untracked}
                </div>
            </div>
        </div>

        {if $AdvancedStatus.Staged}
        <div class="container">
            <h2>Commit</h2>
            <div class="container">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">Author</span>
                        <input type="text" name="author" class="form-control" disabled value="{$.User->FirstName} {$.User->LastName} <{$.User->Email}>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">Subject</span>
                        <input type="text" name="subject" class="form-control" placeholder="Title">
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">Description</span>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="form-group text-right">
                    <div class="btn-group">
                        <button class="btn btn-default" id="js-commit"><i class="glyphicon glyphicon-save"></i> Commit</button>
                    </div>
                </div>
            </div>
        </div>
        {/if}
    {/if}




{/block}

{block "js-bottom"}
    {$dwoo.parent}
    {jsmin "site-admin/git.js"}
{/block}