Ext.define('EmergenceConsole.controller.Viewport', {
    extend: 'Ext.app.Controller',
    requires: [
        'Ext.plugin.Viewport'
    ],


    // controller configurations
    views: [
        'Viewport'
    ],

    refs: {
        viewport: {
            selector: 'app-viewport',
            autoCreate: true,

            xtype: 'app-viewport',
            plugins: 'viewport'
        }
    },


    // controller templates method overrides
    onLaunch: function () {
        this.getViewport();
    }
});