/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * An icon cell providing a delete tool
 */
Ext.define('Jarvus.table.DeleteCell', {
	extend: 'Jarvus.table.IconCell'
	,xtype: 'tabledeletecell'

	,iconCls: 'table-icon-delete'
	,href: '#delete'
});