/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A rawfield for large values of currency with cents stripped
 */
Ext.define('Jarvus.field.BigMoney', {
	extend: 'Jarvus.field.Money'
	,xtype: 'bigmoneyfield'
	
	,precision: 0
	,maskRe: /[0-9$,]/
});