/*jslint browser: true, undef: true *//*global Ext,EmergenceConsole*/
/**
 * The main application class. An instance of this class is created by app.js when it
 * calls Ext.application(). This is the ideal place to handle application launch and
 * initialization details.
 */
Ext.define('EmergenceConsole.Application', {
    extend: 'Ext.app.Application',
    requires: [
        'Ext.window.MessageBox',
        'EmergenceConsole.proxy.API',
        'EmergenceConsole.proxy.WebDavAPI'
    ],

    name: 'EmergenceConsole',

    controllers: [
        'Viewport',

        'Sites',
        'Hosts',

        'Changes',
        'Files',
        'Docs'
    ],

    /*
    *  check the url for an apiHost parameter and set the API hostname if it exists.
    */
    init: function() {
        var pageParams = Ext.Object.fromQueryString(location.search);

        if (pageParams.apiHost) {
            EmergenceConsole.proxy.API.setHost(pageParams.apiHost);
            EmergenceConsole.proxy.WebDavAPI.setHost(pageParams.apiHost);
        }

        Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    },

    onAppUpdate: function () {
        Ext.Msg.confirm('Application Update', 'This application has an update, reload?',
            function (choice) {
                if (choice === 'yes') {
                    window.location.reload();
                }
            }
        );
    }
});