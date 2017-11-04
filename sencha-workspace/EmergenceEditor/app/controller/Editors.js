Ext.define('EmergenceEditor.controller.Editors', {
    extend: 'Ext.app.Controller',
    requires: [
        'Ext.util.KeyMap',
        'Ext.window.MessageBox',

        /* global EmergenceEditor */
        'EmergenceEditor.DAV'
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
        }
    },

    control: {
        tabPanel: {
            tabchange: 'onTabChange',
            staterestore: 'onTabsStateRestore'
        },
        'acepanel': {
            dirtychange: 'onAcePanelDirtyChange'
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
        var path = card.isXType('acepanel') && card.getPath();

        if (path) {
            this.getApplication().setActiveView('/'+path, card.getTitle());
        }
    },

    onAcePanelDirtyChange: function(acePanel, dirty) {
        acePanel.tab.toggleCls('is-dirty', dirty);
    },

    onSaveKey: function() {
        var card = this.getTabPanel().getActiveTab(),
            tab = card.tab;

        if (!card.isXType('acepanel') || !card.isDirty()) {
            return;
        }

        tab.addCls('is-saving');
        card.withContent(function(content) {
            EmergenceEditor.DAV.uploadFile(card.getPath(), content, function(options, success, response) {
                tab.removeCls('is-saving');

                if (success) {
                    card.markClean();
                }

                if (!success && response.status) {
                    Ext.Msg.alert('Failed to save', 'Your changes failed to save to the server');
                }
            });
        });
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
            msg: 'Opening ' + path.substr(path.lastIndexOf('/') + 1) + '&hellip;'
        });

        if (activate) {
            tabPanel.setActiveTab(editor);
        }

        EmergenceEditor.DAV.downloadFile(path, function(options, success, response) {
            editor.loadContent(response.responseText, function () {
                editor.setLoading(false);
            });
        });
    },

    // init: function() {
    //     var me = this;
    //         // app = me.application;

    //     // console.info('Emergence.Editor.controller.Editors.init()');

    //     // Start listening for events on views
    //     // me.control({
    //     //     'emergence-tabpanel':
    //     // });

    //     // app.on({
    //     //     scope: me,
    //     //     fileopen: 'onFileOpen',
    //     //     filesave: 'onFileSave',
    //     //     fileclose: 'onFileClose',
    //     //     diffopen: 'onDiffOpen'
    //     // });
    // },

    // onTabChange: function(tabPanel, newCard, oldCanel) {
    //     var token = newCard.itemId;

    //     if (token) {
    //         this.application.setActiveView(token, newCard.title);
    //     }

    //     var activeCard = this.getTabPanel().getActiveTab();

    //     if (activeCard.xtype == 'acepanel' && typeof activeCard.aceEditor !== 'undefined') {
    //         activeCard.onResize();
    //     }
    // },

    // onDiffOpen: function(path, autoActivate, sideA, sideB) {
    //     autoActivate = autoActivate !== false; // default to true

    //     var itemId, title;

    //     title = path.substr(path.lastIndexOf('/')+1) + ' (' + sideA + '&mdash;' + sideB + ')';
    //     itemId = 'diff:[' + sideA + ',' + sideB + ']/'+path;

    //     var tab = this.getTabPanel().getComponent(itemId);

    //     if (!tab) {
    //         tab = this.getTabPanel().add({
    //             xtype: 'emergence-diff-viewer',
    //             path: path,
    //             sideAid: sideA,
    //             sideBid: sideB,
    //             title: title,
    //             closable: true,
    //             html: '<div></div>'
    //         });
    //     }

    //     if (autoActivate) {
    //         this.getTabPanel().setActiveTab(tab);
    //     }
    // },

    // onFileOpen: function(path, autoActivate, id, line) {

    //     autoActivate = autoActivate !== false; // default to true

    //     var itemId, title;

    //     if (id) {
    //         itemId = 'revision:[' + id + ']/'+path;
    //         title = path.substr(path.lastIndexOf('/')+1) + '(' + id + ')';
    //     } else {
    //         itemId = '/' + path;
    //         title = path.substr(path.lastIndexOf('/')+1);
    //     }

    //     var tab = this.getTabPanel().getComponent(itemId);

    //     if (!tab) {
    //         // TODO: use forceCreate ref
    //         tab = this.getTabPanel().add({
    //             xtype: 'acepanel',
    //             openPath: path,
    //             openLine: line,
    //             title: title,
    //             revisionID: id,
    //             persistent: !id
    //         });
    //     }

    //     if (autoActivate) {
    //         this.getTabPanel().setActiveTab(tab);
    //     }
    // },

    // onFileSave: function() {

    //     var activeCard = this.getTabPanel().getActiveTab();

    //     if (activeCard.xtype == 'aceeditor') {
    //         activeCard.saveFile();
    //     }
    // },

    // onFileClose: function() {

    //     var activeCard = this.getTabPanel().getActiveTab();

    //     if (activeCard.closable) {
    //         activeCard.close();
    //     }
    // }
});