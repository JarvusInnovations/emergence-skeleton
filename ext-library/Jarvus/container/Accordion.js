/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,POC*/
/**
 * @deprecated
 */
Ext.define('Jarvus.container.Accordion', {
	extend: 'Ext.Container'
	,xtype: 'accordion'
	,config:{
		direction: 'horizontal'
		,cls: 'accordion'
	}
	,initialize: function(){
		
		var me = this
			,newItems = [];
		
		me.callParent(arguments);
		
		me.setLayout({
			type: me.config.direction == 'horizontal' ? 'hbox' : 'vbox'
			,align: 'stretch'
		});
		
	}
});