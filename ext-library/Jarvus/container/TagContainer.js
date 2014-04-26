/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.container.TagContainer', {
	extend: 'Ext.Container'
	,xtype: 'tagcontainer'
	
	,tag: null
	
	,getElementConfig: function() {
		var me = this
			,config = me.callParent();
		
		if(Ext.isString(me.tag)) {
			config.tag = me.tag;
		}
		else if(Ext.isObject(me.tag)) {
			Ext.apply(config, me.tag);
		}
		
		return config;
	}
});