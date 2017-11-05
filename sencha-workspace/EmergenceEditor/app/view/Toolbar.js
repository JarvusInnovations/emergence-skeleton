Ext.define('EmergenceEditor.view.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    xtype: 'emergence-toolbar',


    items: [
        {
            action: 'save',
            text: 'Save',
            iconCls: 'x-fa fa-floppy-o',
            tooltip: 'Save current editor (Ctrl+s)'
        },
        '->',
        {
            action: 'open-book',
            text: 'Open Book',
            iconCls: 'x-fa fa-book',
            tooltip: 'Launch emergence book',
            href: 'https://emergenceplatform.gitbooks.io/emergence-book/',
            hrefTarget: '_blank'
        },
        {
            action: 'open-forums',
            text: 'Open Forums',
            iconCls: 'x-fa fa-comments-o',
            tooltip: 'Launch emergence forums',
            href: 'http://forum.emr.ge/',
            hrefTarget: '_blank'
        }
    ]
});