/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A component producing only an hr tag to create a horizontal rule between container items
 */
Ext.define('Jarvus.widget.HorizontalRule', {
	extend: 'Ext.Component'
	,xtype: 'hrule'
	
	,autoEl: 'hr'
	,height: 0
});