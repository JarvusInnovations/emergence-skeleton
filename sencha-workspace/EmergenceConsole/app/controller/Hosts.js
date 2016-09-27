/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.controller.Hosts', {
    extend: 'Ext.app.Controller',

    // entry points
    routes: {
        'hosts': 'showHostsConsole'
    },

    // controller configuration
    views: [
        'hosts.Container',
        'hosts.Toolbar'
    ],

    refs: {
        'appViewport' : 'app-viewport',
        'console' : 'hosts-container'
    },

    // route handlers
    showHostsConsole: function() {
        var me = this;

        me.getAppViewport().getLayout().setActiveItem(me.getConsole());
    }
});
