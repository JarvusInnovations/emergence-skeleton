/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A class for a rawfield that renders a read-only value via an XTemplate
 */
Ext.define('Jarvus.field.Template', {
	extend: 'Jarvus.field.Field'
	,xtype: 'templatefield'
	
	/**
	 * @cfg {String/Ext.XTemplate}
	 * Template to render value
	 */
	,tpl: '{value}'
	
	// field template methods	
	,onChange: function(value) {
		this.update({
			value: value
		});
	}
});