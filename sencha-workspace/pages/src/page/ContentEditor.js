/*jslint browser: true, undef: true *//*global Ext*/
// @require-package emergence-cms
Ext.define('Site.page.ContentEditor', {
    singleton: true,
    requires: [
        'Site.Common',
        'Emergence.cms.view.DualView'
    ],

    constructor: function() {
        Ext.onReady(this.onDocReady, this);
    },

    onDocReady: function() {
        var contentEditorCt = Ext.getBody().down('#contentEditorCt'),
            dualView;

        // initialize QuickTips
        Ext.QuickTips.init();

        // empty content editor container
        contentEditorCt.empty();

        // render dual-view content editor
        dualView = Ext.create('Emergence.cms.view.DualView', {
            renderTo: contentEditorCt
        });

        dualView.lookupReference('editor').setContentRecord(window.ContentData);

        // recalculate content editor layout on window resize
        Ext.on('resize', function() {
            dualView.doLayout();
        }, dualView, { buffer: 100 });
    }
});