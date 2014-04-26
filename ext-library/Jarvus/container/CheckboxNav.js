/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,POC, Jarvus*/
/**
 * @deprecated
 */
Ext.define('Jarvus.container.CheckboxNav', {
	extend: 'Ext.Container'
	,xtype: 'checkboxnav'
	,config: {
		store: null
		,groupField: null
		,displayField: null
		,scrollable: true
		,valueField: null
		,expandFirst: false
		,layout: 'fit'
		,cls: 'checkbox-nav'
	}
});