/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.changes.Grid', {
    extend: 'Ext.grid.Panel',
    xtype: 'changes-grid',

    store: 'changes.ActivityStream',

    viewConfig: {
        loadMask: false
    },

    columns: [{
        text: 'User',
        dataIndex: 'Author',
        renderer: function(author) {
            if (author) {
                return author.Username;
            }
        }
    },{
        text: 'Event',
        dataIndex: 'EventType'
    },{
        text: 'Handle',
        dataIndex: 'Handle',
        flex: 1
    },{
        text: 'Revisions',
        dataIndex: 'revisionsCount'
    },{
        text: 'Time',
        dataIndex: 'Timestamp',
        xtype: 'datecolumn',
        format: 'Y-m-d h:i:s A'
    }]
});
