Ext.define('EmergenceEditor.view.FilesTree', {
    extend: 'Ext.tree.Panel',
    xtype: 'emergence-filestree',
    requires: [
        'Ext.tree.plugin.TreeViewDragDrop'
    ],


    stateId: 'editor-filestree',
    stateful: true,
    store: 'FilesTree',
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