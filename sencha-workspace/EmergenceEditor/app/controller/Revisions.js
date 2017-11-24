Ext.define('EmergenceEditor.controller.Revisions', {
    extend: 'Ext.app.Controller',


    views: [
        'menu.Revision'
    ],

    stores: [
        'Revisions'
    ],

    refs: {
        editorTabPanel: 'emergence-tabpanel',
        revisionsGrid: 'emergence-revisionsgrid',
        revisionMenu: {
            selector: 'emergence-revisionmenu',
            autoCreate: true,

            xtype: 'emergence-revisionmenu'
        }
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
        'emergence-revisionmenu menuitem[action=revision-open]': {
            click: 'onOpenClick'
        },
        // 'emergence-revisionsmenu > menuitem[action=properties]': {
        //     click: this.onPropertiesClick
        // },
        // 'emergence-revisionsmenu > menuitem[action=compare_latest]': {
        //     click: this.onCompareLatestClick
        // },
        // 'emergence-revisionsmenu > menuitem[action=compare_next]': {
        //     click: this.onCompareNextClick
        // },
        // 'emergence-revisionsmenu > menuitem[action=compare_previous]': {
        //     click: this.onComparePreviousClick
        // },
    },


    // event handlers
    onEditorTabChange: function(editorTabPanel, card) {
        var revisionsGrid = this.getRevisionsGrid(),
            revisionsStore = this.getRevisionsStore(),
            revisionsProxy = revisionsStore.getProxy();

        if (card.isXType('emergence-editortab')) {
            revisionsProxy.setExtraParam('path', card.getPath());
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
        var revisionMenu = this.getRevisionMenu();

        event.stopEvent();

        revisionMenu.setRevision(revision);
        revisionMenu.down('[action=revision-compare-latest]').setDisabled(index == 0);
        revisionMenu.down('[action=revision-compare-next]').setDisabled(index == 0);
        revisionMenu.down('[action=revision-compare-previous]').setDisabled(index + 1 == revisionsGrid.getStore().getCount());

        revisionMenu.showAt(event.getXY());

    },

    onOpenClick: function(menuItem) {
        this.redirectTo(menuItem.up('menu').getRevision());
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
    // },
    // onOpenClick: function(menuItem, event, options) {
    //     this.openRevisionByRecord(this.currentRecord);
    // },
    // onCompareLatestClick: function(menuItem, event, options) {
    //     this.openDiff(this.currentRecord, this.currentRecord.store.data.get(0));
    // },
    // onCompareNextClick: function(menuItem, event, options) {
    //     this.openDiff(this.currentRecord, this.currentRecord.store.data.get(this.currentIndex-1));
    // },
    // onComparePreviousClick: function(menuItem, event, options) {
    //     this.openDiff(this.currentRecord.store.data.get(this.currentIndex+1), this.currentRecord);
    // },
    // openDiff: function(sideA, sideB) {
    //     Ext.util.History.add('diff:[' + sideA.get('ID') + ',' + sideB.get('ID') + ']/'+sideA.get('FullPath'), true);
    // }
});