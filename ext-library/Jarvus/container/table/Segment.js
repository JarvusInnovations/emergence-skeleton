/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A container for a group of rows within a table.
 * 
 * Creates a tbody tag by default but can be reconfigured for thead or tfoot by setting {@link #autoEl}.
 */
Ext.define('Jarvus.container.table.Segment', {
	extend: 'Jarvus.container.Raw'
	,xtype: 'tablesegment'
	,requires: [
		'Jarvus.container.table.Row'
	]
	
	/**
	 * Type of element to create. Acceptable values are "tbody", "thead", and "tfoot"
	 */
	,autoEl: 'tbody'
	
	// children should be rows
	,defaultType: 'tablerow'
});