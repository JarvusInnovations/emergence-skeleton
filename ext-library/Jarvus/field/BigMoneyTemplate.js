/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A template field for rendering currency values with no cents
 */
Ext.define('Jarvus.field.BigMoneyTemplate', {
	extend: 'Jarvus.field.Template'
	,xtype: 'bigmoneytemplatefield'
	
	,tpl: '{value:currency("$", 0)}'
});