/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.changes.Container', {
    extend: 'Ext.Container',
    xtype: 'changes-container',

    layout: 'fit',

    items: [{
        xtype: 'changes-grid'
    }]

});
