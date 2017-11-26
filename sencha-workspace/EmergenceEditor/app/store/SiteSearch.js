Ext.define('EmergenceEditor.store.SiteSearch', {
    extend: 'Ext.data.Store',
    requires: [
        'EmergenceEditor.API',
        'Jarvus.proxy.API'
    ],


    model: 'EmergenceEditor.model.SearchResult',

    config: {
        proxy: {
            type: 'api',
            connection: 'EmergenceEditor.API',
            url: '/editor/search',
            reader: {
                type: 'json',
                rootProperty: 'data'
            }
        }
    }
});