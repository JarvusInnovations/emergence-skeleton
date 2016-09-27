/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.FolderContextMenu', {
    extend: 'Ext.menu.Menu',
    xtype: 'files-foldercontextmenu',

    config: {
        rec: null
    },

    items: [{
        text: 'New File',
        iconCls: 'x-fa fa-file',
        action: 'newfile'
    },{
        text: 'New Folder',
        iconCls: 'x-fa fa-folder',
        action: 'newfolder'
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
