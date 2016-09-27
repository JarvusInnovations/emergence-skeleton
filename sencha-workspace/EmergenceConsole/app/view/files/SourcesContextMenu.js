/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.SourcesContextMenu', {
    extend: 'Ext.menu.Menu',
    xtype: 'files-sourcescontextmenu',

//    vertical: true,
/*
    defaults: {
        scale: 'small',
        iconAlign: 'top',
        ui: 'menu-button'
    },
*/

    items: [{
        text: 'New File',
        iconCls: 'x-fa fa-file',
        action: 'newfolder'
    },{
        text: 'New Folder',
        iconCls: 'x-fa fa-folder',
        action: 'newfile'
    },{
        text: 'Rename',
        iconCls: 'x-fa fa-eraser',
        action: 'rename'
    },{
        text: 'Refresh',
        iconCls: 'x-fa fa-refresh',
        action: 'refresh'
    },{
        text: 'Delete',
        iconCls: 'x-fa fa-times',
        action: 'delete'
    }]

});
