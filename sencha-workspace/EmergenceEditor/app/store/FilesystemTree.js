Ext.define('EmergenceEditor.store.FilesystemTree', {
    extend: 'Ext.data.TreeStore',
    alias: 'store.filestree',
    requires: [
        'EmergenceEditor.API',
        'Jarvus.proxy.API'
    ],


    model: 'EmergenceEditor.model.FilesystemNode',

    folderSort: true,
    sortOnLoad: true,
    nodeParam: null,
    sorters: [{
        property: 'Handle',
        direction: 'ASC'
    }],

    root: {
        text: 'children',
        id: 'children',
        expanded: true
    },
    proxy: {
        type: 'api',
        connection: 'EmergenceEditor.API',
        url: function(operation) {
            var node = operation.node;

            return '/develop/json/' + (node.isRoot() ? '' : node.get('FullPath'));
        }
    },
    refreshNodeByRecord: function(record) {
        this.load({
            node: record
        });
    }
    // ,clearOnLoad: false
    /*
    // proxy INSTANCE was required when trying to parse an XML response who's root wasn't "children"
    ,constructor: function() {
        this.proxy = Ext.create('EmergenceEditor.proxy.Develop');

        return this.callParent(arguments);
    }
    */
});