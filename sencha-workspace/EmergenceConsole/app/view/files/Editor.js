/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.Editor', {
    extend: 'Jarvus.ace.Editor',
    xtype: 'files-editor',

    requires: [
        'EmergenceConsole.view.files.AceConfiguration'
    ],

    config: {
        path: null,
        originalValue: null,

        // subcribe to ACE events
        subscribe: {
            'Editor': [
                'change'
            ],
            'EditorSession': [
                'changeMode'
            ]
        }
    },

    initAce: function() {
        var me = this;

        me.callParent();

        // override Ace's default key bindings to intercept save
        me.getAce().commands.addCommand({
            name: "save",
            bindKey: {
                win: "Ctrl-S",
                mac: "Command-S",
                sender: "editor|cli"
            },
            exec: Ext.bind(me.fireEvent,me,['saverequest',me])
        });
    },

    isDirty: function() {
        var me = this;

        return me.originalValue!==me.ace.getValue();
    },

    loadFile: function(text,contentType) {
        var me = this,
            ace = this.getAce();

        if (ace) {
            if (text) {
                ace.setOption('mode',me.getFileMode(contentType));
                ace.setValue(text,-1);
                ace.resize();
            }
            me.setOriginalValue(text);
        } else {
            console.warn('ace editor not available!'); // sanity check: should not arrive here TODO: remove me
        }
    },

    getFileMode: function(contentType) {
        switch(contentType)
        {
            case 'application/javascript':
                return 'ace/mode/javascript';

            case 'application/php':
                return 'ace/mode/php';

            case 'text/html':
            case 'text/x-c++':
            case 'text/plain':
                return 'ace/mode/html';

            case 'text/css':
                return 'ace/mode/css';

            case 'text/x-scss':
                return 'ace/mode/scss';

            case 'text/x-dwoo':
            case 'text/x-smarty':
            case 'text/x-html-template':
                return 'ace/mode/html';

            default:
                return false;
        }
    }

});
