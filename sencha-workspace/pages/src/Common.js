/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Site.Common', {
    singleton: true,
    requires: [
        'Ext.dom.Element'
    ],

    constructor: function() {
        Ext.onReady(this.onDocReady, this);
    },

    onDocReady: function() {
        var me = this,
            body = Ext.getBody();

        
    }
});