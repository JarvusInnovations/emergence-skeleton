/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.AceConfiguration', {
    override: 'Jarvus.ace.util.AbstractConfiguration',

    config: {
        options: {
            theme: 'ace/theme/chaos',
            fontSize: 14,
            highlightActiveLine: true,
            showPrintMargin: true,
            showInvisibles: true,
            showGutter: true
        }
    }
});
