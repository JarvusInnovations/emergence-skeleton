/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.FileContextMenu', {
    extend: 'Ext.menu.Menu',
    xtype: 'files-filecontextmenu',

    config: {
        rec: null
    },

    items: [{
        text: 'Open',
        iconCls: 'x-fa fa-file',
        action: 'open'
    },{
        text: 'Properties',
        iconCls: 'x-fa fa-folder',
        action: 'properties'
    },{
        text: 'Rename',
        iconCls: 'x-fa fa-eraser',
        action: 'rename'
    },{
        text: 'Delete',
        iconCls: 'x-fa fa-times',
        action: 'delete'
    }]

});
