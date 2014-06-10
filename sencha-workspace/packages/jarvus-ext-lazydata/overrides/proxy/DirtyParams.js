/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Jarvus.ext.override.proxy.DirtyParams', {
    override: 'Ext.data.proxy.Server',

    setExtraParam: function(name, value) {
        var me = this,
            extraParams = me.extraParams;

        if (extraParams[name] !== value) {
            me.markParamsDirty();
            extraParams[name] = value;
        }
    },

    markParamsDirty: function() {
        this.extraParamsDirty = true;
    },
    
    clearParamsDirty: function() {
        this.extraParamsDirty = false;
    },

    isExtraParamsDirty: function() {
        return this.extraParamsDirty;
    },

    buildRequest: function() {
        this.clearParamsDirty();
        return this.callParent(arguments);
    }
});