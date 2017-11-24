Ext.define('EmergenceEditor.controller.Revisions', {
    extend: 'Ext.app.Controller',


    views: [
        'tab.Diff',
        'menu.Revision'
    ],

    stores: [
        'Revisions'
    ],

    refs: {
        editorTabPanel: 'emergence-tabpanel',
        revisionsGrid: 'emergence-revisionsgrid',

        menu: {
            selector: 'emergence-revisionmenu',
            autoCreate: true,

            xtype: 'emergence-revisionmenu'
        },
        openMenuItem: 'emergence-revisionmenu menuitem[action=revision-open]',
        propertiesMenuItem: 'emergence-revisionmenu menuitem[action=revision-properties]',
        compareLatestMenuItem: 'emergence-revisionmenu menuitem[action=revision-compare-latest]',
        compareNextMenuItem: 'emergence-revisionmenu menuitem[action=revision-compare-next]',
        comparePreviousMenuItem: 'emergence-revisionmenu menuitem[action=revision-compare-previous]'
    },

    control: {
        editorTabPanel: {
            tabchange: 'onEditorTabChange'
        },
        revisionsGrid: {
            expand: 'onRevisionsGridExpand',
            itemdblclick: 'onRevisionDoubleClick',
            itemcontextmenu: 'onRevisionContextMenu'
        },
        openMenuItem: {
            click: 'onOpenClick'
        },
        // propertiesMenuItem: {
        //     click: this.onPropertiesClick
        // },
        compareLatestMenuItem: {
            click: 'onCompareLatestClick'
        },
        compareNextMenuItem: {
            click: 'onCompareNextClick'
        },
        comparePreviousMenuItem: {
            click: 'onComparePreviousClick'
        },
    },


    // event handlers
    onEditorTabChange: function(editorTabPanel, card) {
        var revisionsGrid = this.getRevisionsGrid(),
            revisionsStore = this.getRevisionsStore(),
            revisionsProxy = revisionsStore.getProxy(),
            path;

        if (
            (
                card.isXType('emergence-editortab')
                && (path = card.getPath())
            )
            || (
                card.isXType('emergence-difftab')
                && (path = card.getLeftPath())
                && path == card.getRightPath()
            )
        ) {
            revisionsProxy.setExtraParam('path', path);
            revisionsGrid.enable();

            if (!revisionsGrid.getCollapsed() && revisionsProxy.isExtraParamsDirty()) {
                revisionsStore.load();
            }
        } else {
            revisionsStore.removeAll();
            revisionsProxy.setExtraParams({});
            revisionsProxy.clearParamsDirty();
            revisionsGrid.disable();
        }
    },

    onRevisionsGridExpand: function() {
        var revisionsStore = this.getRevisionsStore();

        if (revisionsStore.getProxy().isExtraParamsDirty()) {
            revisionsStore.load();
        }
    },

    onRevisionDoubleClick: function(gridView, revision) {
        this.redirectTo(revision);
    },

    onRevisionContextMenu: function(revisionsGrid, revision, itemDom, index, event) {
        var me = this,
            menu = me.getMenu();

        event.stopEvent();

        menu.setRevision(revision);

        me.getCompareLatestMenuItem().setDisabled(index == 0);
        me.getCompareNextMenuItem().setDisabled(index == 0);
        me.getComparePreviousMenuItem().setDisabled(index + 1 == revisionsGrid.getStore().getCount());

        menu.showAt(event.getXY());
    },

    onOpenClick: function() {
        this.redirectTo(this.getMenu().getRevision());
    },

    onCompareLatestClick: function() {
        var me = this,
            store = me.getRevisionsStore(),
            revision = this.getMenu().getRevision();

        this.redirectToDiff(revision, store.getAt(0));
    },

    onCompareNextClick: function() {
        var me = this,
            store = me.getRevisionsStore(),
            revision = this.getMenu().getRevision();

        this.redirectToDiff(revision, store.getAt(store.indexOf(revision)-1));
    },

    onComparePreviousClick: function() {
        var me = this,
            store = me.getRevisionsStore(),
            revision = this.getMenu().getRevision();

        this.redirectToDiff(store.getAt(store.indexOf(revision)+1), revision);
    },


    // local methods
    redirectToDiff: function(leftRevision, rightRevision) {
        this.redirectTo('diff?' + this.getTabDiffView().buildToken({
            leftPath: leftRevision.get('FullPath'),
            leftRevision: leftRevision.getId(),
            rightPath: rightRevision.get('FullPath'),
            rightRevision: rightRevision.getId()
        }));
    }




    // legacy
    // onPropertiesClick: function(menuItem, event, options) {
    //     var data = this.currentRecord.raw;

    //     var html = '';

    //     for (var key in data) {
    //         html += key + ': ' + data[key] + '<br>\n';
    //     }

    //     Ext.create('Ext.window.Window', {
    //         title: data.Handle,
    //         height: 300,
    //         width: 375,
    //         layout: 'fit',
    //         html: html
    //     }).show();
    // }
});