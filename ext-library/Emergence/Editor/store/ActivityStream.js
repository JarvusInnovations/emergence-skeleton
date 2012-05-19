Ext.define('Emergence.Editor.store.ActivityStream', {
    extend: 'Ext.data.Store'
    ,alias: 'store.activitystream'
    //,autoLoad: true
    ,model: 'Emergence.Editor.model.ActivityEvent'
    ,proxy: {
        type: 'ajax'
        ,url: '/editor/activity'
        ,reader: {
            type: 'json'
            ,root: 'data'
        }
    }
});