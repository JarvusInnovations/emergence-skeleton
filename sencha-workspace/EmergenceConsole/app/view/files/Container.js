/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.Container', {
    extend: 'Ext.Container',
    xtype: 'files-container',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    items: [{
        xtype: 'container',
        width: 220,
        layout: {
            type: 'vbox',
            align: 'stretch'
        },
        items: [{
            xtype: 'files-openfilesgrid',
            title: 'Open Files',
            flex: 1
        },{
            xtype: 'files-sourcestreepanel',
            title: 'Sources',
            flex: 3
        }]
    },{
        xtype: 'files-editorcontainer',
        flex: 1,
        items: [{
            xtype: 'container' // dummy card, helps when removing cards
        }]
    }]

});
