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
                ':path': '([^@\\$]+)'
            }
        },
        '/:path@:revision': {
            action: 'showPathRevision',
            conditions: {
                ':path': '([^@\\$]+)',
                ':revision': '(\\d+)'
            }
        },
        '/:path\\$:line': {
            action: 'showPathLine',
            conditions: {
                ':path': '([^@\\$]+)',
                ':line': '(\\d+)'
            }
        },
        '/:path@:revision\\$:line': {
            action: 'showPathRevisionLine',
            conditions: {
                ':path': '([^@\\$]+)',
                ':revision': '(\\d+)',
                ':line': '(\\d+)'
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
    showPath: function(path) {
        this.openPath(path);
    },

    showPathRevision: function(path, revision) {
        this.openPath(path, {
            revision: revision
        });
    },

    showPathLine: function(path, line) {
        this.openPath(path, {
            line: line
        });
    },

    showPathRevisionLine: function(path, revision, line) {
        this.openPath(path, {
            revision: revision,
            line: line
        });
    },


    // event handlers
    onTabsStateRestore: function(tabPanel, state) {
        var openFiles = state.openFiles || [],
            openFilesLength = openFiles.length,
            openFileIndex = 0, openFile;

        for (; openFileIndex < openFilesLength; openFileIndex++) {
            openFile = openFiles[openFileIndex];
            this.openPath(openFile.path, Ext.applyIf({ activate: false }, openFile));
        }
    },

    onTabChange: function(tabPanel, card) {
        var isEditor = card.isXType('acepanel'),
            saveBtn = this.getSaveBtn(),
            token, revision, line;

        if (isEditor) {
            token = '/' + card.getPath();
            revision = card.getRevision();
            line = card.getLine();

            if (revision) {
                token += '@'+revision;
            }

            if (line) {
                token += '$'+line;
            }

            this.getApplication().setActiveView(token, card.getTitle());
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
    openPath: function(path, options) {
        options = options || {};

        // eslint-disable-next-line vars-on-top
        var me = this,
            tabPanel = me.getTabPanel(),
            revision = options.revision || null,
            line = options.line || null,
            activate = options.activate !== false,
            editor = tabPanel.items.findBy(function(card) {
                return card.isXType('acepanel') && card.getPath() === path && card.getRevision() === revision;
            });

        if (editor) {
            editor.setLine(line);
        } else {
            editor = me.getEditorPanel({
                path: path,
                revision: revision,
                line: line
            });

            tabPanel.add(editor);

            editor.setLoading({
                msg: 'Opening ' + Jarvus.ace.Util.basename(path) + '&hellip;'
            });

            EmergenceEditor.DAV.downloadFile({
                url: path,
                revision: revision
            }).then(function(response) {
                editor.loadContent(response.responseText, function () {
                    editor.setLoading(false);
                });
            });
        }

        if (activate) {
            tabPanel.setActiveTab(editor);
        }
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