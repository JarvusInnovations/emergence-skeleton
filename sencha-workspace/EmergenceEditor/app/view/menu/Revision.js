Ext.define('EmergenceEditor.view.menu.Revision', {
    extend: 'Ext.menu.Menu',
    xtype: 'emergence-revisionmenu',


    config: {
        revision: null
    },

    items: [
        {
            text: 'Open',
            action: 'revision-open',
            iconCls: 'x-fa fa-file-o'
        },
        {
            text: 'Properties',
            action: 'revision-properties',
            iconCls: 'x-fa fa-list',
            disabled: true // TODO: reimplement
        },
        {
            text: 'Compare Latest',
            action: 'revision-compare-latest',
            iconCls: 'x-fa fa-fast-forward'
        },
        {
            text: 'Compare Next',
            action: 'revision-compare-next',
            iconCls: 'x-fa fa-arrow-up'
        },
        {
            text: 'Compare Previous',
            action: 'revision-compare-previous',
            iconCls: 'x-fa fa-arrow-down'
        }
    ]
});