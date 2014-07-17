/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Emergence.cms.view.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    xtype: 'emergence-cms-toolbar',
    requires:[
//      'Ext.tab.Panel',
//      'Ext.layout.container.Border',
        'Ext.form.field.Checkbox',
        'Ext.form.field.Date',
        'Ext.form.field.Time',
        'Emergence.ext.proxy.Records'
//         'ExtUx.form.field.BoxSelect',
//         'ExtUx.DateTimeField'
    ],

    items:[
        {
            xtype: 'button',
            text: 'Save',
    
            action: 'save'
        },
//        '-',
//        {
//            reference: 'openBtn',
//    
//            xtype: 'button',
//            text: 'Open',
//            href: '#',
//            disabled: true
//        },
        '->',
        {
            reference: 'statusBtn',
    
            xtype: 'button',
            text: 'Set status',
            menu: {
//                plain: true,
                items: [
                   '<b class="menu-title">Publish status</b>',
                    {
                        xtype: 'menucheckitem',
                        group: 'status',
                        text: 'Draft',
                        value: 'Draft',
                        glyph: '68',
                        tooltip: 'This content is not complete and will only be accessible to the author via the "my drafts" page'
                    },
                    {
                        xtype: 'menucheckitem',
                        group: 'status',
                        text: 'Published',
                        value: 'Published',
                        glyph: '80',
                        tooltip: 'Anyone online can find or view this content'
        
                    }
                ]
            }
        },
        {
            reference: 'visibilityBtn',
    
            xtype: 'button',
            text: 'Set visibility',
            menu: {
//                plain: true,
                items: [
                    '<b class="menu-title">Visibility</b>',
                    {
                        xtype: 'menucheckitem',
                        group: 'visibility',
                        text: 'Login required',
                        value: 'Private',
                        glyph: '76',
                        tooltip: 'Only users logged into the site can find or view this content'
                    },
                    {
                        xtype: 'menucheckitem',
                        group: 'visibility',
                        text: 'Public',
                        value: 'Public',
                        glyph: '80',
                        tooltip: 'Anyone online can find or view this content'
                    }
                ]
            }
        },
        {
            reference: 'publishedTimeBtn',
    
            xtype: 'button',
            text: 'Set publication time',
            menu: {
                plain: true,
                width: 200,
                items: [
                    '<b class="menu-title">Publication time</b>',
                    {
                        xtype: 'menucheckitem',
                        text: 'Publish on save'
                    },
                    '-',
                    {
                        xtype: 'datefield'
                    },
                    {
                        xtype: 'timefield',
                        submitFormat: 'H:i'
                    }
                ]
            }
        }
    ]
});