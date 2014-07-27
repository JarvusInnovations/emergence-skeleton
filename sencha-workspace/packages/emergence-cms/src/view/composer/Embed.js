/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Emergence.cms.view.composer.Embed', {
    extend: 'Emergence.cms.view.composer.Abstract',
    alias: 'emergence-cms-composer.embed',
    requires: [
        'Ext.form.field.TextArea'
    ],

    inheritableStatics: {
        contentItemClass: 'Emergence\\CMS\\Item\\Embed',
        buttonCfg: {
            text: 'Embed Code',
            glyph: 0xf121+'@FontAwesome', // fa-code
            cls: 'icon-w-30',
            tooltip: 'Add a section containing an HTML embed code (from YouTube, etc).'
        }
    },

    title: 'HTML Embed Code',
    height: 100,
    items: [{
        xtype: 'textarea'
    }],

    initComponent: function() {
        var me = this,
            editorValue = me.contentItem ? me.contentItem.Data : '',
            textarea;

        me.callParent();

        textarea = me.textarea = me.down('textarea').setValue(editorValue);
        textarea.on('change', 'onTextareaChange', me);
    },

    onTextareaChange: function(textarea, value) {
        var me = this;
        
        me.getPreviewHtml(function(html) {
            me.fireEvent('previewchange', me, html);
        });
    },

    getPreviewHtml: function(callback) {
        callback(this.textarea.getValue());
    },

    getItemData: function() {
        return Ext.applyIf({
            Class: 'Emergence\\CMS\\Item\\Embed',
            Data: this.textarea.getValue()
        }, this.callParent());
    }
});