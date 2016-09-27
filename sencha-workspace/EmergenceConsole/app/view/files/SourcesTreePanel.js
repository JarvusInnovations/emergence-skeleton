/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.SourcesTreePanel', {
    extend: 'Ext.tree.Panel',
    xtype: 'files-sourcestreepanel',

    store: 'files.Sources',

    viewConfig: {
        loadMask: false
    },

    rootVisible: false
});
