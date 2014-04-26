/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * An icon cell providing a collapse/expand tool
 */
Ext.define('Jarvus.table.ExpandCell', {
	extend: 'Jarvus.table.IconCell'
	,xtype: 'tableexpandcell'

	,iconCls: 'table-icon-collapsed'
	,href: '#expand'
});