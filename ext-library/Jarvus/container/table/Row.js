/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A container for cells within a table row
 * 
 * Creates a tr tag and overrides autoEl to td for child components by default. Because of the
 * item default autoEl=td, no components should be added directly to a row that already use a
 * speciality autoEl like forms and table-wrapped fields.
 */
Ext.define('Jarvus.container.table.Row', {
	extend: 'Jarvus.container.Raw'
	,xtype: 'tablerow'
	
	,autoEl: 'tr'
	,defaults: {
		xtype: 'component'
		,autoEl: 'td'
	}
	
	/**
	 * @cfg {Object/Object[]} items
	 * List of child components to be rendered as cells within the row
	 * 
	 * @cfg {String} [items.xtype=component]
	 * @cfg {String} [items.autoEl=td] Child components must use td or th for autoEl
	 */
});