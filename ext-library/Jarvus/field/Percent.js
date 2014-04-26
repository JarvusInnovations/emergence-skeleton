/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A rawfield for percent values that gets rendered in 0-100 but set/get in 0-1
 */
Ext.define('Jarvus.field.Percent', {
	extend: 'Jarvus.field.Float'
	,xtype: 'percentfield'
	,requires: [
		'Ext.util.Format'
	]
	
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
	
	,monitorChange: true
	,inputPattern: false //'-?(\\.\\d+|\\d+\\.?\\d*)%?'
	,maskRe: /[0-9%,.]/
	,componentCls: 'field-percent'

	,rawToValue: function(rawValue) {
		return !rawValue && rawValue !== 0 ? null : this.capValue(rawValue/100);
	}
	
	,valueToRaw: function(value) {
		return !value && value !== 0 ? null : value*100;
	}
	
	,transformRawValue: function(value) {
		return this.allowBlank && !value && value !== 0 ? '' : Ext.util.Format.round(value||0, this.precision)+'%';
	}
});