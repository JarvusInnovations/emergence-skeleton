/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A template field for rendering percent values with in the 0-100 scale
 */
Ext.define('Jarvus.field.PercentTemplate', {
	extend: 'Jarvus.field.Template'
	,xtype: 'percenttemplatefield'
	
	,tpl: '{[fm.round(values.value*100, 2)]}%'
});