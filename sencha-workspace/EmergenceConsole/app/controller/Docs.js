/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.controller.Docs', {
    extend: 'Ext.app.Controller',

    // entry points
    routes: {
        'sites/docs': 'showDocsView'
    },

    // controller configuration
    views: [
        'docs.Container'
    ],

    refs: {
        'appViewport': 'app-viewport',
        'sitesContainer': 'sites-container',
        'sitesContent': 'sites-container > #content',

        'docsContainer': {
            selector: 'docs-container',
            xtype: 'docs-container',
            forceCreate: true
        }
    },

    // route handlers
    showDocsView: function() {
        var me = this;

        me.getAppViewport().getLayout().setActiveItem(me.getSitesContainer());
        me.getSitesContent().setActiveItem(me.getDocsContainer());
    }
});
