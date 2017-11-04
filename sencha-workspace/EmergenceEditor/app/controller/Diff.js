Ext.define('EmergenceEditor.controller.Diff', {
    extend: 'Ext.app.Controller',
    require: [
        'Ext.Promise',

        /* global EmergenceEditor */
        'EmergenceEditor.DAV'
    ],


    views: [
        'ace.DiffPanel@Jarvus'
    ],

    routes: {
        'diff\\?:query': {
            action: 'showDiff',
            conditions: {
                ':query': '(.+)'
            }
        }
    },

    refs: {
        tabPanel: 'tabpanel',
        diffPanel: {
            selector: 'acediffpanel',
            forceCreate: true,

            xtype: 'acediffpanel',
            closable: true,
            title: 'Compare'
        }
    },


    showDiff: function(queryString) {
        var me = this,
            query = Ext.Object.fromQueryString(queryString),
            from = query.from.split('@'),
            to = query.to.split('@'),
            fromPath = from[0],
            fromRevision = from[1] || null,
            toPath = to[0] || fromPath,
            toRevision = to[1] || null;

        Ext.Promise.all([
            EmergenceEditor.DAV.downloadFile({
                url: fromPath,
                revision: fromRevision
            }),
            EmergenceEditor.DAV.downloadFile({
                url: toPath,
                revision: toRevision
            })
        ]).then(function(responses) {
            var tabPanel = me.getTabPanel(),
                diffPanel = me.getDiffPanel({
                    left: {
                        path: fromPath,
                        revision: fromRevision,
                        content: responses[0].responseText
                    },
                    right: {
                        path: toPath,
                        revision: toRevision,
                        content: responses[1].responseText
                    }
                });

            tabPanel.add(diffPanel);
            tabPanel.setActiveTab(diffPanel);
        })
    }
});