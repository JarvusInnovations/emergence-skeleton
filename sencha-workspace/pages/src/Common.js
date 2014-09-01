/*jslint browser: true, undef: true *//*global Ext*/
// @require-package jarvus-hotfixes-ext-5.0.1.1255
Ext.define('Site.Common', {
    singleton: true,
    requires: [],

    constructor: function() {
        Ext.onReady(this.onDocReady, this);
    },

    onDocReady: function() {
        var me = this,
            body = Ext.getBody();

        
    }
});