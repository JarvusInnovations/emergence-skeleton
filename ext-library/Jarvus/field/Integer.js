/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A rawfield subclass for a text field that only accepts integer numbers
 */
Ext.define('Jarvus.field.Integer', {
	extend: 'Jarvus.field.Input'
	,xtype: 'integerfield'
	
	,inputCls: 'integer'
	,inputPattern: '[\\-\\d.]+'
	,componentCls: 'field-integer'
	,maskRe: /[0-9,]/
	
	,processRawValue: function(rawValue) {
		return rawValue.replace(/[^\-\d.]/g, '');
	}
	
	,rawToValue: function(rawValue) {
		return !rawValue && rawValue !== 0 ? null : parseInt(rawValue, 10);
	}
});