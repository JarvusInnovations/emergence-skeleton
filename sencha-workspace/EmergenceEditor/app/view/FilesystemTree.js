Ext.define('EmergenceEditor.view.FilesystemTree', {
    extend: 'Ext.tree.Panel',
    xtype: 'emergence-filesystemtree',
    requires: [
        'Ext.tree.plugin.TreeViewDragDrop',
        'Ext.grid.plugin.CellEditing',
        'Ext.form.field.Text'
    ],


    title: 'Filesystem',
    stateful: true,
    stateId: 'editor-filesystemtree',
    plugins: {
        id: 'cellediting',
        ptype: 'cellediting',
        clicksToEdit: 1
    },

    store: 'FilesystemTree',
    useArrows: true,
    rootVisible: false,
    autoScroll: true,
    scrollDelta: 10,
    multiSelect: true,
    viewConfig: {
        loadMask: false,
        plugins: {
            ptype: 'treeviewdragdrop',
            appendOnly: true,
            dragText: '{0} selected item{1}',
            containerScroll: true
        }
    },

    columns: [
        {
            flex: 1,

            xtype: 'treecolumn',
            dataIndex: 'Handle',
            align: 'left',
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }
    ]
});