/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.container.DummyForm', {
	extend: 'Jarvus.container.TagContainer'
	,xtype: 'dummyform'
	
	,tag: {
		tag: 'form'
		,action: '#'
		,onsubmit: 'return false'
	}
});