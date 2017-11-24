/**
 * Responsible for the general integration between the main tabPanel and navigation
 */
Ext.define('EmergenceEditor.controller.Tabs', {
    extend: 'Ext.app.Controller',


    refs: {
        tabPanel: 'tabpanel'
    },

    control: {
        tabPanel: {
            tabchange: 'onTabChange',
            beforetabmenu: 'onBeforeTabMenu'
        },
    },


    // event handlers
    onTabChange: function(tabPanel, card) {
        this.getApplication().setActiveView(card.isTabbable ? card.buildFullToken() : card.staticToken, card.getTitle());
    },

    onBeforeTabMenu: function(menu, card) {
        var isTabbable = card.isTabbable,
            tearItem = menu.getComponent('tear'),
            pageParams, url;

        if (!isTabbable) {
            if (tearItem) {
                tearItem.hide();
            }
            return;
        }

        pageParams = Ext.applyIf({
            fullscreen: true
        }, this.getApplication().launchParams);

        url = '?' + Ext.urlEncode(pageParams) + '#' + card.buildFullToken();

        if (tearItem) {
            tearItem.itemEl.set({
                href: url
            });
            tearItem.show();
        } else {
            menu.insert(0, [
                {
                    itemId: 'tear',
                    text: 'Link to fullscreen',
                    hrefTarget: '_blank',
                    href: url
                },
                {
                    xtype: 'menuseparator'
                }
            ]);
        }
    }
});