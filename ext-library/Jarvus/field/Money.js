/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A rawfield for currency values
 */
Ext.define('Jarvus.field.Money', {
	extend: 'Jarvus.field.Float'
	,xtype: 'moneyfield'
	,requires: [
		'Ext.util.Format'
	]
	
	/**
	 * @cfg {String}
	 * The currency symbol to use
	 */
	,sign: '$'
	
	/**
	 * @cfg {Boolean}
	 * False to convert blank to 0
	 */
	,allowBlank: false
	
	/**
	 * @cfg {Number}
	 * How many decimals to include when rendering in readable form
	 */
	,precision: 2
	
	,inputPattern: false //'-?$?(\\.\\d+|[\\d,]+\\.?\\d*)'
	,monitorChange: true
	,maskRe: /[0-9$,.]/
	,componentCls: 'field-money'
	
	,transformRawValue: function(value) {
		return this.allowBlank && !value && value !== 0 ? '' : Ext.util.Format.currency(value, this.sign, this.precision);
	}
});