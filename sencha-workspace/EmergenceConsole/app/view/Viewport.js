/**
 * This class is the main view for the application. It is specified in app.js as the
 * "mainView" property. That setting automatically applies the "viewport"
 * plugin causing this view to become the body element (i.e., the viewport).
 */
Ext.define('EmergenceConsole.view.Viewport', {
    extend: 'Ext.Container',
    xtype: 'app-viewport',

    layout: 'card',

    items: [{
        xtype: 'sites-container'
    },{
        xtype: 'hosts-container'
    }]

});
