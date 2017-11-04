Ext.define('EmergenceEditor.controller.Activity', {
    extend: 'Ext.app.Controller',


    stores: [
        'ActivityStream'
    ],

    refs: {
        activityPanel: 'emergence-activity'
    },

    control: {
        activityPanel: {
            activate: 'onActivityPanelActivate'
        },
        'emergence-activity button[action=refresh]': {
            click: 'onRefreshClick'
        },
        'emergence-activity button[action=load-all]': {
            click: 'onLoadAllClick'
        }
    },


    onActivityPanelActivate: function() {
        var store = this.getActivityStreamStore();

        if (!store.isLoaded() && !store.isLoading()) {
            store.load();
        }
    },

    onRefreshClick: function() {
        this.getActivityStreamStore().load();
    },

    onLoadAllClick: function() {
        this.getActivityStreamStore().load({
            url: '/editor/activity/all'
        });
    }
});