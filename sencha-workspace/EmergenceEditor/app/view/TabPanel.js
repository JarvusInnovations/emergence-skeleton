Ext.define('EmergenceEditor.view.TabPanel', {
    extend: 'Ext.tab.Panel',
    xtype: 'emergence-tabpanel',
    requires: [
        'Ext.ux.TabCloseMenu',
        'Ext.ux.TabScrollerMenu',
        'Ext.ux.TabReorderer'
    ],


    componentCls: 'emergence-tabpanel',
    stateful: true,
    stateId: 'editorTabs',
    stateEvents: ['remove', 'add'],

    plugins: [
        {
            ptype: 'tabclosemenu',
            listeners: {
                beforemenu: function(menu, card, plugin) {
                    this.getCmp().fireEvent('beforetabmenu', menu, card, plugin);
                }
            }
        },
        'tabreorderer',
        'tabscrollermenu'
    ],

    items: [{
        xtype: 'emergence-activity',
        itemId: 'activity'
    }],


    // lifecycle methods
    getState: function() {
        var openFiles = [],
            items = this.items,
            itemsCount = items.getCount(),
            itemIndex = 0,
            item;

        for (; itemIndex < itemsCount; itemIndex++) {
            item = items.get(itemIndex);

            if (item.isXType('acepanel')) {
                openFiles.push({
                    path: item.getPath(),
                    revision: item.getRevision()
                });
            }
        }

        return { openFiles: openFiles };
    }
});