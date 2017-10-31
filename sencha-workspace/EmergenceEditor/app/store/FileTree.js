/* jslint browser: true, undef: true, white: false, laxbreak: true *//* global Ext, EmergenceEditor*/
Ext.define('EmergenceEditor.store.FileTree', {
    extend: 'Ext.data.TreeStore',
    alias: 'store.filetree',
    requires: [
        'EmergenceEditor.API',
        'Jarvus.proxy.API'
    ],


    model: 'EmergenceEditor.model.File',

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
        url: '/develop/json/'
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