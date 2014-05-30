/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.touch.override.proxy.DirtyParams', {
    override: 'Ext.data.proxy.Server'
	
	,extraParamsDirty: false
	
	,setExtraParam: function(name, value) {
		var extraParams = this.getExtraParams();
		
		if (extraParams[name] !== value) {
			this.markParamsDirty();
			extraParams[name] = value;
		}
	}
    
    ,patchExtraParams: function(newParams) {
        var extraParams = this.getExtraParams()
            ,dirty = false;

        Ext.Object.each(newParams, function(name, value) {
            if (extraParams[name] !== value) {
                dirty = true;
                extraParams[name] = value;
            }
        });

        Ext.Array.each(Ext.Array.difference(Ext.Object.getKeys(extraParams), Ext.Object.getKeys(newParams)), function(name) {
            dirty = true;
            delete extraParams[name];
        });

        if (dirty) {
            this.markParamsDirty();
        }
    }
	
	,markParamsDirty: function() {
		this.extraParamsDirty = true;
	}
	
	,isExtraParamsDirty: function() {
		return this.extraParamsDirty;
	}

	,buildRequest: function() {
		this.extraParamsDirty = false;
		return this.callParent(arguments);
	}
});