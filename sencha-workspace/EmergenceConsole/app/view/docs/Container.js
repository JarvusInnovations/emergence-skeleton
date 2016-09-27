/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.docs.Container', {
    extend: 'Ext.Container',
    xtype: 'docs-container',

    layout: 'fit',

    items: [{
        xtype: 'box',
        autoEl : {
            tag : "iframe",
            src : "https://themightychris.gitbooks.io/emergence-book/content/"
        }
    }]

});
