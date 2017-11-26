Ext.define('EmergenceEditor.store.FilesystemTree', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'EmergenceEditor.API',
        'Jarvus.proxy.API'
    ],


    model: 'EmergenceEditor.model.FilesystemNode',

    config: {
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
        }
    }
});