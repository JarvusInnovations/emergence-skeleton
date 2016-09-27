/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.sites.Menu', {
    extend: 'Ext.toolbar.Toolbar',
    xtype: 'sites-menu',

    defaults: {
        scale: 'small',
        iconAlign: 'top'
    },

    items: [{
        text: 'Changes',
        iconCls: 'x-fa fa-area-chart',
        route: 'sites/changes'
    },{
        text: 'Files',
        iconCls: 'x-fa fa-files-o',
        route: 'sites/files'
    },{
        text: 'Sources',
        iconCls: 'x-fa fa-cloud-download'
    },{
        text: 'Shell',
        iconCls: 'x-fa fa-square'
    },{
        text: 'Docs',
        iconCls: 'x-fa fa-info-circle',
        route: 'sites/docs'
    }]

});
