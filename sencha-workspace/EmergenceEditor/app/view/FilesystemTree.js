Ext.define('EmergenceEditor.view.FilesystemTree', {
    extend: 'Ext.tree.Panel',
    xtype: 'emergence-filesystemtree',
    requires: [
        'Ext.tree.plugin.TreeViewDragDrop'
    ],


    stateId: 'editor-filesystemtree',
    stateful: true,
    store: 'FilesystemTree',
    title: 'Filesystem',
    useArrows: true,
    rootVisible: false,
    autoScroll: true,
    scrollDelta: 10,
    multiSelect: true,
    viewConfig: {
        loadMask: false,
        plugins: {
            ptype: 'treeviewdragdrop',
            pluginId: 'ddplugin',
            appendOnly: true,
            dragText: '{0} selected item{1}',
            containerScroll: true
        }
    },

    columns: [
        {
            xtype: 'treecolumn',
            dataIndex: 'Handle',
            align: 'left',
            flex: 1
        }
    ]
});