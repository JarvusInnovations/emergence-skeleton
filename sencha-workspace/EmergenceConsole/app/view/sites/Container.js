/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.sites.Container', {
    extend: 'Ext.Panel',
    xtype: 'sites-container',

    layout: 'fit',

    items : [{
        itemId: 'content',
        xtype: 'panel',
        layout: 'card'
    }],

    dockedItems: [{
        xtype: 'sites-toolbar',
        dock: 'top'
    },{
        xtype: 'sites-menu',
        dock: 'left'
    }]

});
