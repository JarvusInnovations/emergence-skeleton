Ext.define('EmergenceEditor.view.viewport.FullEditor', {
    extend: 'Ext.container.Container',
    requires: [
        'EmergenceEditor.view.TabPanel',
        'EmergenceEditor.view.TransfersGrid',

        'Ext.layout.container.Border'
    ],


    layout: 'border',

    initComponent: function() {
        // console.info('Emergence.Editor.view.Viewport.initComponent()');


        this.items = [{
            xtype: 'emergence-tabpanel',
            region: 'center',
            singleFile: true,
            listeners: {
                scope: this,
                tabchange: function(tabPanel, newCard, oldCard) {
                    tabPanel.getTabBar().show();
                    tabPanel.doComponentLayout();
                },
                remove: function(tabPanel, oldCard) {
                    if (!tabPanel.is('emergence-tabpanel')) {
                        return true;
                    }

                    if (tabPanel.items.getCount() == 1) {
                        tabPanel.getTabBar().hide();
                        tabPanel.doComponentLayout();
                    }
                }
            }
        }, {
            xtype: 'emergence-transfersgrid',
            region: 'south',
            height: 100,
            collapsed: true,
            collapsible: true,
            split: true,
            icon: '/img/icons/fugue/system-monitor-network.png'
        }];

        this.callParent();
    }
});