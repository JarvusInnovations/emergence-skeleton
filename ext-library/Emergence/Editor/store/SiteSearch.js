Ext.define('Emergence.Editor.store.SiteSearch', {
    extend: 'Ext.data.Store'
    ,alias: 'store.sitesearch'
    //,autoLoad: true
    ,model: 'Emergence.Editor.model.SearchResult'
    ,proxy: {
        type: 'ajax'
        ,url: '/editor/search'
        ,reader: {
            type: 'json'
            ,root: 'data'
        }
    }
});