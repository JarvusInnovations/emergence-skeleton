Ext.define('EmergenceEditor.view.viewport.IDE', {
    extend: 'Ext.container.Container',
    requires: [
        'EmergenceEditor.view.Menubar',
        'EmergenceEditor.view.FilesTree',
        'EmergenceEditor.view.TabPanel',
        'EmergenceEditor.view.Revisions',

        'Ext.layout.container.Border'
    ],


    layout: 'border',

    initComponent: function() {
        // console.info('Emergence.Editor.view.Viewport.initComponent()');


        this.items = [{
            xtype: 'emergence-menubar',
            region: 'north'
            // ,html: 'Emergence Development Environment'
        }, {
            xtype: 'emergence-filestree',
            region: 'west',
            stateId: 'viewport-files',
            stateful: true,
            title: 'Files',
            width: 200,
            collapsible: true,
            split: true
        }, {
            xtype: 'emergence-tabpanel',
            region: 'center'
        }, {
            //            xtype: 'tabpanel'
            title: 'Revision History',
            xtype: 'emergence-file-revisions',
            icon: '/img/icons/fugue/edit-diff.png',
            region: 'east',
            stateId: 'viewport-details',
            width: 275,
            collapsible: true,
            collapsed: true,
            split: true
            //            ,preventHeader: true
            //            ,items: [
            //                {
            //                    title: 'Revisions'
            //                    ,xtype: 'emergence-file-revisions'
            //                    ,icon: '/img/icons/fugue/edit-diff.png'
            //                }
            //                /*,{
            //                    title: 'Code Navigator'
            //                }*/
            //            ]
        }];

        this.callParent();

    }

});