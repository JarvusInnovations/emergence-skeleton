/* jslint browser: true, undef: true, white: false, laxbreak: true *//* global Ext, EmergenceEditor*/
Ext.define('EmergenceEditor.store.Revisions', {
    extend: 'Ext.data.Store',
    alias: 'store.revisions',
    requires: [
        'EmergenceEditor.API',
        'Jarvus.proxy.API'
    ],

    storeId: 'revisions',
    // ,autoLoad: true
    fields: [
        { name: 'ID',
            type: 'integer' },
        'Class',
        'Handle',
        'Type',
        'MIMEType',
        { name: 'Size',
            type: 'integer' },
        'SHA1',
        'Status',
        { name: 'Timestamp',
            type: 'date',
            dateFormat: 'timestamp' },
        'Author',
        { name: 'AuthorID',
            type: 'integer' },
        { name: 'AncestorID',
            type: 'integer' },
        { name: 'CollectionID',
            type: 'integer' },
        'FullPath'
    ],

    sorters: [{
        property: 'Timestamp',
        direction: 'DESC'
    }],


    proxy: {
        type: 'api',
        connection: 'EmergenceEditor.API',
        url: '/editor/getRevisions/',
        reader: {
            type: 'json',
            rootProperty: 'revisions'
        }
    }
});