/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Site.Common', {
    singleton: true,
    requires: [
        //'Site.widget.ContentBlocks',
        'Site.widget.Search',
        'Site.widget.model.Person',
        'Site.widget.model.Tag',
        'Site.widget.model.Content'
    ],

    constructor: function() {
        Ext.onReady(this.onDocReady, this);
    },

    onDocReady: function() {
        var me = this,
            body = Ext.getBody();

        // site search
        me.siteSearch = Ext.create('Site.widget.Search', {
            searchForm: body.down('.js-site-search')
        });
    }
});