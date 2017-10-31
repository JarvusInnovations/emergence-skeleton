/* jslint browser: true, undef: true, white: false, laxbreak: true *//* global Ext, EmergenceEditor*/
Ext.define('EmergenceEditor.store.SiteSearch', {
    extend: 'Ext.data.Store',
    alias: 'store.sitesearch',
    requires: [
        'EmergenceEditor.API',
        'Jarvus.proxy.API'
    ],


    // ,autoLoad: true
    model: 'EmergenceEditor.model.SearchResult',
    proxy: {
        type: 'api',
        connection: 'EmergenceEditor.API',
        url: '/editor/search',
        reader: {
            type: 'json',
            rootProperty: 'data'
        }
    }
});