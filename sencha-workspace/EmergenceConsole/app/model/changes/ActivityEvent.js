/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.model.changes.ActivityEvent', {
    extend: 'Ext.data.Model',
    requires: [
        'EmergenceConsole.proxy.Records'
    ],

    proxy: {
        type: 'consolerecords',
        url: '/editor/activity',
        idParam: false
    },

    idProperty: 'href',

    fields: [{
        name: 'EventType',
        type: 'string'
    },{
        name: 'Handle',
        type: 'string'
    },{
        name: 'CollectionPath',
        type: 'string'
    },{
        name: 'FirstTimestamp',
        type: 'date',
        dateFormat: 'timestamp',
        useNull: true
    },{
        name: 'Timestamp',
        type: 'date',
        dateFormat: 'timestamp'
    },{
        name: 'RevisionID',
        type: 'integer'
    },{
        name: 'FirstRevisionID',
        type: 'integer'
    },{
        name: 'FirstAncestorID',
        type: 'integer',
        useNull: true
    },{
        name: 'revisions',
        useNull: true
    },{
        name: 'files',
        useNull: true
    },{
        name: 'revisionsCount',
        convert: function(v, r) {
            var revisions = r.get('revisions');
            return revisions ? revisions.length : null;
        },
        useNull: true
    },{
        name: 'Author'
    },{
        name: 'Collection'
    }]
});
