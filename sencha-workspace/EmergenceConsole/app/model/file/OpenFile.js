/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.model.file.OpenFile', {
    extend: 'Ext.data.Model',

    idProperty: 'filePath',

    fields: [{
        name: 'filePath',
        type: 'string'
    },{
        name: 'fileName',
        type: 'string'
    },{
        name: 'editorId',
        type: 'string'
    },{
        name: 'dirty',
        type: 'boolean',
        defaultValue: false
    }],

    proxy: {
        type: 'localstorage',
        id  : 'open-files'
    }
});
