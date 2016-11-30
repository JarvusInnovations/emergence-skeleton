{extends design.tpl}

{block title}Migrations &mdash; {$dwoo.parent}{/block}

{block content}
    <ol class="breadcrumb">
        <li><a href="/.emr/tools">Tools</a></li>
        <li class="active">Migrations</li>
    </ol>
    <div class="panel panel-default">
        <div class="panel-heading">Migrations</div>
        <table class="table row-stripes row-highlight" id="adminMigrationsTable">
            <thead class="panel-heading">
                <tr>
                    <th scope="col">Migration</th>
                    <th scope="col">Status</th>
                    <th scope="col">Timestamp</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            
            <tbody>
                {foreach item=migration from=$data}
                    <tr>
                        <td class="migration-id">{$migration.key|escape}<br><small>SHA1: {$migration.sha1}</td>
                        <td class="migration-status">{$migration.status}</td>
                        <td class="migration-timestamp">{$migration.executed}</td>
                        <td class="migration-action">
                            {if $migration.status == 'new'}
                                <form action="/.emr/tools/migrations/{$migration.key|escape}" method="POST">
                                    <input type="submit" value="Execute">
                                </form>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    
        <form method="GET">
            <input type="hidden" name="refresh" value="1">
            <input type="submit" value="Refresh Inherited Migrations">
        </form>
    </div>
{/block}

{block js-bottom}
    {$dwoo.parent}
    <script>
    Ext.define('Site.page.admin.Migrations', {
        requires: [
            'Ext.Ajax',
            'Ext.Date'
        ],
        singleton: true,
        
        constructor: function() {
            Ext.onReady(this.onDocReady, this);
        },
        
        onDocReady: function() {
            var me = this,
                tableEl = Ext.get('adminMigrationsTable'),
                forms = [];
            
            tableEl.select('tbody tr').each(function(rowEl) {
                var formEl = rowEl.down('form');
                
                if (!formEl) {
                    return; // skip rows that are already executed
                }
                
                forms.push(formEl);
                
                formEl.on('submit', function(ev, t) {
                    ev.stopEvent();
                    
                    formEl.down('input[type=submit]').set({ disabled: true });

                    Ext.Ajax.request({
                        method: 'POST',
                        form: formEl,
                        headers: {
                            Accept: 'application/json'
                        },
                        callback: function(options, success, response) {
                            var responseData = Ext.decode(response.responseText),
                                migrationData = responseData.data;

                            formEl.up('tr').down('.migration-status').update(migrationData.status);
                            formEl.up('tr').down('.migration-timestamp').update(Ext.Date.format(new Date(migrationData.executed*1000), 'Y-m-d H:i:s'));

                            formEl.destroy();
                            
                            console.groupCollapsed('%s migration %s', migrationData.status, migrationData.key);
                            console.log(responseData.output);
                            console.table(responseData.log);
                            console.groupEnd();
                        }
                    });
                });
            });
        }
    });
    </script>
{/block}