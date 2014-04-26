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