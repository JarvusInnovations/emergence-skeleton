/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.controller.Sites', {
    extend: 'Ext.app.Controller',

    // entry points
    routes: {
        'sites': 'showSitesConsole'
    },

    control: {
        'sites-menu button': {
            'click': 'onMenuButtonClick'
        }
    },

    // controller configuration
    views: [
        'sites.Container',
        'sites.Toolbar',
        'sites.Menu'
    ],

    refs: {
        'appViewport' : 'app-viewport',
        'console' : 'sites-container'
    },

    // route handlers
    showSitesConsole: function() {
        var me = this;

        me.getAppViewport().getLayout().setActiveItem(me.getConsole());
    },

    onMenuButtonClick: function(button) {
        var route = button.route;

        if (route) {
            this.redirectTo(route);
        }
    }
});
