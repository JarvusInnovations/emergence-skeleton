/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A table segment featuring two rows: a primary row that is always visible and a detail row
 * that is revealed when the segment is "expanded" by clicking an arrow at the front
 * of the primary row.
 * 
 * Creates a tbody tag.
 */
Ext.define('Jarvus.container.table.Expando', {
	extend: 'Jarvus.container.table.Segment'
	,xtype: 'tableexpando'
	,requires: [
		'Jarvus.container.table.Row'
		,'Jarvus.container.table.DetailRow'
	]

	/**
	 * @cfg {Object/Object[]} (required)
	 * Either a component config for the primary row in the expando group or an array
	 * of component configs that will be added as items to a {@link Jarvus.container.table.Row table row}.
	 * 
	 * If the row contains a {@link Jarvus.table.ExpandCell tableexpandcell}, its click event will
	 * be observed to trigger expand/collapse
	 */
	,primaryRow: null
	
	/**
	 * @cfg {Object} detailRow (required)
	 * A component config for the detail row in the expando group.
	 * 
	 * @cfg {String} [detailRow.xtype=detailrow]
	 * @cfg {Number} [detailRow.colSpan=the number of items in the primaryRow]
	 */
	,detailRow: null
	
	/**
	 * @cfg {Boolean}
	 * True to render the detailed row expanded initially.
	 */
	,expanded: false


	,initComponent: function() {
		var me = this;
		
		me.addEvents('expand','collapse');
		
		// init expanded state
		if(me.expanded)
		{
			me.addCls('expanded');
		}

		// compose items list
		if(!me.primaryRow || !me.detailRow)
		{
			Ext.Error.raise('Table expando requires primaryRow and detailRow');
		}
		
		// apply defaults
		if(Ext.isArray(me.primaryRow))
		{
			me.primaryRow = {
				xtype: 'tablerow'
				,items: me.primaryRow
			};
		}
		
		Ext.applyIf(me.detailRow, {
			xtype: 'detailrow'
			,colSpan: me.primaryRow.items.length
		});
		
		// set items
		me.items = [me.primaryRow, me.detailRow];
		
		// call parent - init items
		me.callParent();
		
		// find expand icon
		me.expandCell = me.down('tableexpandcell');
		if(me.expandCell)
		{
			me.expandCell.on('click', me.toggleExpand, me);
		}
	}
	
	/**
	 * Expand or collapse the group
	 */
	,toggleExpand: function() {
		this[this.expanded?'collapse':'expand']();
	}

	/**
	 * Expand the group, revealing the detailRow
	 */
	,expand: function() {
		var me = this;

		if(!me.expanded)
		{
			me.addCls('expanded');
			me.expanded = true;
			me.updateLayout();
			me.fireEvent('expand', me);
		}
	}
	
	/**
	 * Collapse the group, hiding the detailRow
	 */
	,collapse: function() {
		var me = this;

		if(me.expanded)
		{
			me.removeCls('expanded');
			me.expanded = false;
			me.updateLayout();
			me.fireEvent('collapse', me);
		}
	}
});