/* jslint browser: true, undef: true, white: false, laxbreak: true *//* global Ext, EmergenceEditor*/
Ext.define('EmergenceEditor.controller.Editors', {
    extend: 'Ext.app.Controller',

    views: ['editor.TabPanel', 'ace.Panel@Jarvus'],
    refs: [{
        ref: 'tabPanel',
        selector: 'tabpanel'
    }],



    init: function() {
        var me = this,
            app = me.application;

        // console.info('Emergence.Editor.controller.Editors.init()');

        // Start listening for events on views
        me.control({
            'emergence-editortabpanel': {
                tabchange: me.onTabChange,
                staterestore: me.onTabsStateRestore
            }
        });

        app.on({
            scope: me,
            fileopen: 'onFileOpen',
            filesave: 'onFileSave',
            fileclose: 'onFileClose',
            diffopen: 'onDiffOpen'
        });
    },
    onTabChange: function(tabPanel, newCard, oldCanel) {
        var token = newCard.itemId;

        if (token) {
            this.application.setActiveView(token, newCard.title);
        }

        var activeCard = this.getTabPanel().getActiveTab();

        if (activeCard.xtype == 'acepanel' && typeof activeCard.aceEditor !== 'undefined') {
            activeCard.onResize();
        }
    },
    onTabsStateRestore: function(tabPanel, state) {

        Ext.each(state.openFiles, function(path) {
            this.onFileOpen(path, false);
        }, this);

    },
    onDiffOpen: function(path, autoActivate, sideA, sideB) {
        autoActivate = autoActivate !== false; // default to true

        var itemId, title;

        title = path.substr(path.lastIndexOf('/')+1) + ' (' + sideA + '&mdash;' + sideB + ')';
        itemId = 'diff:[' + sideA + ',' + sideB + ']/'+path;

        var tab = this.getTabPanel().getComponent(itemId);

        if (!tab) {
            tab = this.getTabPanel().add({
                xtype: 'emergence-diff-viewer',
                path: path,
                sideAid: sideA,
                sideBid: sideB,
                title: title,
                closable: true,
                html: '<div></div>'
            });
        }

        if (autoActivate) {
            this.getTabPanel().setActiveTab(tab);
        }
    },
    onFileOpen: function(path, autoActivate, id, line) {

        autoActivate = autoActivate !== false; // default to true

        var itemId, title;

        if (id) {
            itemId = 'revision:[' + id + ']/'+path;
            title = path.substr(path.lastIndexOf('/')+1) + '(' + id + ')';
        } else {
            itemId = '/' + path;
            title = path.substr(path.lastIndexOf('/')+1);
        }

        var tab = this.getTabPanel().getComponent(itemId);

        if (!tab) {
            // TODO: use forceCreate ref
            tab = this.getTabPanel().add({
                xtype: 'acepanel',
                openPath: path,
                openLine: line,
                title: title,
                closable: true,
                revisionID: id,
                persistent: !id
            });
        }

        if (autoActivate) {
            this.getTabPanel().setActiveTab(tab);
        }
    },
    onFileSave: function() {

        var activeCard = this.getTabPanel().getActiveTab();

        if (activeCard.xtype == 'aceeditor') {
            activeCard.saveFile();
        }
    },
    onFileClose: function() {

        var activeCard = this.getTabPanel().getActiveTab();

        if (activeCard.closable) {
            activeCard.close();
        }
    }
});