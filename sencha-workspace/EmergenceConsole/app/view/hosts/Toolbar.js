/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.hosts.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    xtype: 'hosts-toolbar',

    items: [{
        xtype: 'combobox',
        fieldLabel: 'Hosts',
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
        emptyText: 'Search sites, services and commands'
    }]

});
