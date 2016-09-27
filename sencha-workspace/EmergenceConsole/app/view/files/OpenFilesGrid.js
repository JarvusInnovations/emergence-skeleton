/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.OpenFilesGrid', {
    extend: 'Ext.grid.Panel',
    xtype: 'files-openfilesgrid',

    requires: [
        'EmergenceConsole.store.files.OpenFiles'
    ],

    hideHeaders: true,
    viewConfig: {
        loadMask: false
    },

    store: {
        xclass: 'EmergenceConsole.store.files.OpenFiles'
    },

    columns: [{
        xtype:'actioncolumn',
        width: 18,
        items: [{
            iconCls: 'x-fa fa-times',
            action: 'closefile'
        }]
    },{
        xtype:'actioncolumn',
        width: 18,
        items: [{
            iconCls: 'x-fa fa-floppy-o',
            action: 'savefile',
            disabledCls: 'x-item-hidden', // TODO: not working
            isDisabled: function(view,rowIndex,colIndex,item,rec) {
                return !rec.data.dirty;
            }
        }]
    },{
        text: 'Name',
        dataIndex: 'fileName',
        flex: 1
    }]
});
