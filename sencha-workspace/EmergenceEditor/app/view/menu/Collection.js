Ext.define('EmergenceEditor.view.menu.Collection', {
    extend: 'Ext.menu.Menu',
    xtype: 'emergence-menu-collection',


    config: {
        collection: null
    },

    items: [
        {
            text: 'New File',
            action: 'new-file',
            iconCls: 'x-fa fa-file'
        },
        {
            text: 'New Folder',
            action: 'new-folder',
            iconCls: 'x-fa fa-folder'
        },
        {
            text: 'Refresh',
            action: 'refresh',
            iconCls: 'x-fa fa-refresh'
        },
        {
            text: 'Rename',
            action: 'rename',
            iconCls: 'x-fa fa-pencil'
        },
        {
            text: 'Delete',
            action: 'delete',
            iconCls: 'x-fa fa-trash'
        }
    ],


    // config handlers
    updateCollection: function(collection) {
        var me = this,
            isLocal = collection.get('Site') == 'Local';

        me.child('[action=rename]').setDisabled(!isLocal);
        me.child('[action=delete]').setDisabled(!isLocal);
    }
});