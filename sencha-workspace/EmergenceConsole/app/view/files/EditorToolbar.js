/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.EditorToolbar', {
    extend: 'Ext.toolbar.Toolbar',
    xtype: 'files-editortoolbar',

    items: [{
        xtype: 'tbfill'
    },{
        iconCls: 'x-fa fa-cog',
        action: 'settings'
    }]

});
