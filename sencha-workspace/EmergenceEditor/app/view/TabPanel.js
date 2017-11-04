Ext.define('EmergenceEditor.view.TabPanel', {
    extend: 'Ext.tab.Panel',
    xtype: 'emergence-tabpanel',
    requires: [
        'Ext.ux.TabCloseMenu',
        'Ext.ux.TabScrollerMenu',
        'Ext.ux.TabReorderer'
    ],


    config: {
        componentCls: 'emergence-tabpanel'
    },


    listeners: {
        tabchange: function(tabpanel, newcard, oldcard, options) {
            var revisionsPanel = EmergenceEditor.app.viewport.down('emergence-file-revisions');

            if (newcard.itemId == 'activity') {
                revisionsPanel.getStore().removeAll();
                revisionsPanel.collapse();
            } else if (newcard.ID) {
                if (revisionsPanel.isVisible(true)) {
                    revisionsPanel.store.load({ params: { ID: newcard.ID } });
                }
            }
        },

        /* ,activate: function(tabpanel, options)
        {
            console.log(this);
            EmergenceEditor.app.viewport.down('emergence-file-revisions').up().on('expand', function(viewportEast, options) {
                console.log(this);
            }, this);
        }*/
        scope: this
    },

    initComponent: function() {

        var me = this;

        if (this.singleFile) {
            this.tabBar = this.tabBar || {};
            this.tabBar.hidden = true;
        } else {
            // todo: extend this plugin so we can use our own menu items
            this.plugins = [
                Ext.create('Ext.ux.TabCloseMenu', {
                    listeners: {
                        scope: this,
                        beforemenu: function(menu, item) {
                            var tearItem = menu.getComponent('tear');
                            var urlParams = Ext.urlDecode(location.search);

                            urlParams.fullscreen = 1;

                            var url = '?'+Ext.urlEncode(urlParams)+'#'+item.itemId;

                            if (tearItem) {
                                tearItem.el.down('.x-menu-item-link').dom.href = url;
                            } else {
                                menu.insert(0, { xtype: 'menuseparator' });
                                tearItem = menu.insert(0, {
                                    itemId: 'tear',
                                    text: 'Link to fullscreen',
                                    hrefTarget: '_blank',
                                    href: url
                                });
                            }
                        }
                    }
                }),
                //                ,Ext.create('Ext.ux.TabScrollerMenu', { maxText  : 50, pageSize: 30 })
                Ext.create('Ext.ux.TabReorderer')
            ];

            this.items = [{
                xtype: 'emergence-activity',
                itemId: 'activity'
            }];
        }

        this.callParent(arguments);
    },

    /* implement statefulness for open tabs */
    stateful: true,
    stateId: 'editorTabs',
    stateEvents: ['remove', 'add'],


    getState: function() {
        var openFiles = [],
            items = this.items,
            itemsCount = items.getCount(),
            itemIndex = 0,
            item, path;

        for (; itemIndex < itemsCount; itemIndex++) {
            item = items.get(itemIndex);

            if (item.isXType('acepanel') && (path = item.getPath())) {
                openFiles.push(path);
            }
        }

        return { openFiles: openFiles };
    }
});