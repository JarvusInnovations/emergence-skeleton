/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.store.files.Sources', {
    extend: 'Ext.data.TreeStore',

    model: 'EmergenceConsole.model.file.File',

    autoLoad: false,

    root: {
        expanded: false
    }

});
