/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.hosts.Container', {
    extend: 'Ext.tab.Panel',
    xtype: 'hosts-container',

    layout: 'fit',

    ui: 'navigation',
    tabPosition: 'left',
    tabRotation: 0,

    defaults: {
        iconAlign: 'top'
    },

    items: [{
        iconCls: 'x-fa fa-sitemap',
        title: 'Sites',
        html: 'Sites'
    },{
        iconCls: 'x-fa fa-tasks',
        title: 'Services',
        html: 'Service'
    },{
        iconCls: 'x-fa fa-heartbeat',
        title: 'Health',
        html: 'Health'
    },{
        iconCls: 'x-fa fa-list',
        title: 'Logs',
        html: 'Logs'
    }],

    dockedItems: [{
        xtype: 'hosts-toolbar',
        docked: 'top'
    }]

});
