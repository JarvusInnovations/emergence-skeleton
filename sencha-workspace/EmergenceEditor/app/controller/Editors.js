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


    // controller config
    views: [
        'tab.Editor'
    ],

    routes: {
        '/:token': {
            action: 'showToken',
            conditions: {
                ':token': '(.+)'
            }
        }
    },

    refs: {
        saveBtn: 'emergence-toolbar button[action=save]',

        tabPanel: 'tabpanel',

        editorTab: {
            forceCreate: true,

            xtype: 'emergence-editortab',
            title: 'Editor'
        }
    },

    control: {
        tabPanel: {
            tabchange: 'onTabChange'
        },
        'emergence-editortab': {
            activate: 'onEditorActivate',
            dirtychange: 'onEditorDirtyChange'
        },
        saveBtn: {
            click: 'onSaveBtnClick'
        }
    },


    // controller lifecycle
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
    showToken: function(token) {
        var me = this,
            tabPanel = me.getTabPanel(),
            editorTab = tabPanel.findUsableTab('emergence-editortab', token);

        if (editorTab) {
            editorTab.setToken(token);
        } else {
            editorTab = tabPanel.add(me.getEditorTab({
                token: token
            }));
        }

        tabPanel.setActiveTab(editorTab);
    },


    // event handlers
    onTabChange: function(tabPanel, card) {
        var saveBtn = this.getSaveBtn();

        if (saveBtn) {
            saveBtn.setDisabled(!card.isSavable || !card.isDirty());
        }
    },

    onEditorActivate: function(editorTab) {
        if (!editorTab.getLoadNeeded()) {
            return;
        }

        editorTab.setLoadNeeded(false);
        editorTab.setLoading({
            msg: 'Opening ' + editorTab.getTitle() + '&hellip;'
        });

        EmergenceEditor.DAV.downloadFile({
            url: editorTab.getPath(),
            revision: editorTab.getRevision()
        }).then(function(response) {
            editorTab.loadContent(response.responseText, function () {
                editorTab.setLoading(false);
            });
        });
    },

    onEditorDirtyChange: function(editorTab, dirty) {
        editorTab.tab.toggleCls('is-dirty', dirty);
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
    saveActive: function() {
        var card = this.getTabPanel().getActiveTab(),
            tab = card.tab;

        if (!card.isXType('emergence-editortab') || !card.isDirty()) {
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