/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.sites.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    xtype: 'sites-toolbar',

    items: [{
        xtype: 'combobox',
        fieldLabel: 'Sites',
        labelAlign: 'right',
        labelWidth: 48,
        store: {
            fields:['value','text'],
            data: [
                {value: 'www.google.com',  text: 'www.google.com'},
                {value: 'www.ibm.com',  text: 'www.ibm.com'}
            ]
        }
    },{
        xtype: 'textfield',
        name: 'Search',
        flex: 10,
        emptyText: 'Search files and commands'
    }]

});
