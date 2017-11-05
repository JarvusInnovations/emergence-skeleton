Ext.define('EmergenceEditor.controller.Editors', {
    extend: 'Ext.app.Controller',
    requires: [
        'Ext.util.KeyMap',
        'Ext.window.MessageBox',

        /* global EmergenceEditor */
        'EmergenceEditor.DAV',

        /* global Jarvus */
        'Jarvus.ace.Util'
    ],


    views: [
        'ace.Panel@Jarvus'
    ],

    routes: {
        '/:path': {
            action: 'showPath',
            conditions: {
                ':path': '(.+)'
            }
        }
    },

    refs: {
        tabPanel: 'tabpanel',

        editorPanel: {
            selector: 'acepanel',
            forceCreate: true,

            xtype: 'acepanel',
            closable: true,
            title: 'Editor'
        },

        saveBtn: 'emergence-toolbar button[action=save]'
    },

    control: {
        tabPanel: {
            staterestore: 'onTabsStateRestore',
            tabchange: 'onTabChange',
            beforetabmenu: 'onBeforeTabMenu'
        },
        'acepanel': {
            dirtychange: 'onAcePanelDirtyChange'
        },
        saveBtn: {
            click: 'onSaveBtnClick'
        }
    },


    // lifecycle methods
    onLaunch: function() {
        var me = this;

        // init keymap
        me.keyMap = Ext.create('Ext.util.KeyMap', {
            target: document,
            binding: [{
                key: 's',
                ctrl: true,
                defaultEventAction: 'stopEvent',
                scope: me,
                handler: me.onSaveKey
            // }, {
            //     key: 'f',
            //     ctrl: true,
            //     defaultEventAction: 'stopEvent',
            //     scope: me,
            //     handler: me.onFindKey
            }]
        });
    },


    // route handlers
    showPath: function(path, line) {
        this.openPath(path, line);
    },


    // event handlers
    onTabsStateRestore: function(tabPanel, state) {
        var openFiles = state.openFiles || [],
            openFilesLength = openFiles.length,
            openFileIndex = 0;

        for (; openFileIndex < openFilesLength; openFileIndex++) {
            this.openPath(openFiles[openFileIndex], null, false);
        }
    },

    onTabChange: function(tabPanel, card) {
        var isEditor = card.isXType('acepanel'),
            path = isEditor && card.getPath(),
            saveBtn = this.getSaveBtn();

        if (path) {
            this.getApplication().setActiveView('/'+path, card.getTitle());
        }

        if (saveBtn) {
            saveBtn.setDisabled(!isEditor || !card.isDirty());
        }
    },

    onBeforeTabMenu: function(menu, card) {
        var tearItem = menu.getComponent('tear'),
            cardPath = card.isXType('acepanel') && card.getPath(),
            params = cardPath && Ext.applyIf({
                fullscreen: true
            }, this.getApplication().launchParams),
            url = cardPath && '?' + Ext.urlEncode(params) + '#/' + cardPath;

        if (tearItem) {
            tearItem.itemEl.set({
                href: url
            });
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
    },

    onAcePanelDirtyChange: function(acePanel, dirty) {
        acePanel.tab.toggleCls('is-dirty', dirty);
        this.getSaveBtn().setDisabled(!dirty);
    },

    onSaveBtnClick: function() {
        this.saveActive();
    },

    onSaveKey: function() {
        this.saveActive();
    },

    // onFindKey: function() {
    //     debugger;
    // },


    // local methods
    openPath: function(path, line, activate) {
        var me = this,
            tabPanel = me.getTabPanel(),
            editor = tabPanel.items.findBy(function(card) {
                return card.isXType('acepanel') && card.getPath() == path;
            });

        activate = activate !== false;

        if (editor) {
            if (activate) {
                tabPanel.setActiveTab(editor);
            }
            return;
        }

        editor = me.getEditorPanel({
            path: path
        });

        tabPanel.add(editor);

        editor.setLoading({
            msg: 'Opening ' + Jarvus.ace.Util.basename(path) + '&hellip;'
        });

        if (activate) {
            tabPanel.setActiveTab(editor);
        }

        EmergenceEditor.DAV.downloadFile(path).then(function(response) {
            editor.loadContent(response.responseText, function () {
                editor.setLoading(false);
            });
        });
    },

    saveActive: function() {
        var card = this.getTabPanel().getActiveTab(),
            tab = card.tab;

        if (!card.isXType('acepanel') || !card.isDirty()) {
            return;
        }

        tab.addCls('is-saving');
        card.withContent(function(content) {
            EmergenceEditor.DAV.uploadFile(card.getPath(), content).then(function(response) {
                tab.removeCls('is-saving');
                card.markClean();
            }).catch(function(response) {
                if (response.status) {
                    Ext.Msg.alert('Failed to save', 'Your changes failed to save to the server');
                }
            });
        });
    }
});