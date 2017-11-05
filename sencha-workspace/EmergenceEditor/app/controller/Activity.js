Ext.define('EmergenceEditor.controller.Activity', {
    extend: 'Ext.app.Controller',


    stores: [
        'ActivityStream'
    ],

    routes: {
        'activity': 'showActivity'
    },

    refs: {
        tabPanel: 'tabpanel',
        activityPanel: 'emergence-activity'
    },

    control: {
        tabPanel: {
            tabchange: 'onTabChange'
        },
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


    // route handlers
    showActivity: function() {
        this.getTabPanel().setActiveTab(this.getActivityPanel());
    },


    // event handlers
    onTabChange: function(tabPanel, card, oldCard) {
        if (oldCard && card === this.getActivityPanel()) {
            this.getApplication().setActiveView('activity', 'Activity');
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