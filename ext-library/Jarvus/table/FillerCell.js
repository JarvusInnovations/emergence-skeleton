/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A component containing only &amp;nbsp; for filling in unused table cells
 */
Ext.define('Jarvus.table.FillerCell', {
	extend: 'Ext.Component'
	,xtype: 'tablefillercell'
	
	,html: '&nbsp;'
});