{extends "design.tpl"}

{block "content"}
    <div class="row emr-tools">
        <div class="col-xs-6 col-lg-4">
            <h2><span class="glyphicon glyphicon-transfer" aria-hidden="true"></span> Precache Parent</h2>
            <p>Update the cache of the parent site locally. If you know the parent site has new files you'll want to run this to allow this site to see them.</p>
            <p><a class="btn btn-default" href="/.emr/tools/precache" role="button">Precache Parent »</a></p>
        </div>
        <div class="col-xs-6 col-lg-4">
            <h2><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span> Pull Parent</h2>
            <p>Pull in code updates from the parent site via Emergence inheritence over HTTP. Comes with a diff tool so you can inspect changes before grabbing them.</p>
            <p><a class="btn btn-default" href="/app/EmergencePullTool/production" role="button">Pull Parent »</a></p>
        </div>
        <div class="col-xs-6 col-lg-4">
            <h2><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Clear APC Cache</h2>
            <p>Clear the site's APC cache. Allows you to isolate APC data for the Emergence Filesystem or the site's other APC data.</p>
            <p><a class="btn btn-default" href="/.emr/tools/clear-apc-cache" role="button">Clear APC Cache »</a></p>
        </div>
        <div class="col-xs-6 col-lg-4">
            <h2><span class="glyphicon glyphicon-import" aria-hidden="true"></span> Migrations</h2>
            <p>Run migration scripts. Occasionally a database structure change requires a migration script to properly apply a code update from the parent site.</p>
            <p><a class="btn btn-default" href="/.emr/tools/migrations" role="button">Migrations »</a></p>
        </div>

        <div class="col-xs-6 col-lg-4">
            <h2><span class="glyphicon glyphicon-hdd" aria-hidden="true"></span> Table Manager</h2>
            <p>Generate database tables directly from Emergence ActiveRecord or VersionedRecord models. Allows you to preview the query before running it.</p>
            <p><a class="btn btn-default" href="/.emr/tools/table-manager" role="button">Table Manager »</a></p>
        </div>
        <div class="col-xs-6 col-lg-4">
            <h2><span class="glyphicon glyphicon-file" aria-hidden="true"></span> Clear Template Cache</h2>
            <p>Delete templates for the site forcing them to be regenerated.</p>
            <p><a class="btn btn-default" href="/.emr/tools/clear-template-cache" role="button">Clear Template Cache »</a></p>
        </div>
    </div>
{/block}