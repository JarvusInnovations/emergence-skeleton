/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A component for creating a FieldTable that is bound to a store for a dynamic
 * number of segments or rows.
 */
Ext.define('Jarvus.container.table.ListFieldTable', {
	extend: 'Jarvus.container.table.FieldTable'
	,xtype: 'listfieldtable'
	,requires: [
		'Ext.data.StoreManager'
		,'Jarvus.container.table.Header'
		,'Ext.window.MessageBox'
	]
	,mixins: {
		bindable: 'Ext.util.Bindable'
	}
	 
	/**
	 * @cfg {String/Ext.data.Store} store (required)
	 * Store instance, config, or storeId to bind to
	 */

	/**
	 * @cfg {Object[]/Object/Ext.Component} headerRow (required)
	 * A component or component config that will create the table's header row.
	 * 
	 * If an array is provided, a {@link Jarvus.container.table.Header tableheader} will
	 * automatically be created for the headerRow and the array passed on for its
	 * {@link Jarvus.container.table.Header#columns tableheader}.
	 */
	 
	/**
	 * @cfg {String} recordRowType (required)
	 * xtype for the {@link Jarvus.container.table.Segment segment} or
	 * {@link Jarvus.container.table.Row row} that will be created as a child
	 * item for each record in the store
	 */

	/**
	 * @cfg {Boolean}
	 * True to automatically expand new segments when they are added
	 */
	,expandOnAdd: true
	
	// template methods for component
	,initComponent: function() {
		var me = this;

		// apply defaults
		if(Ext.isArray(me.headerRow))
		{
			me.headerRow = {
				xtype: 'tableheader'
				,columns: me.headerRow
			};
		}
		
		// set items
		me.items = [me.headerRow];
		
		// call parent -- init items
		me.callParent();
		
		// bind store with Bindable mixin
		me.bindStore(me.store, true);
	}
	
	
	// template methods for Ext.util.Bindable
	,getStoreListeners: function() {
		var me = this;
		return {
			refresh: me.refresh
			,add: me.onRecordAdd
			,remove: me.onRecordRemove
		};
	}
	
	
	// member methods
	,refresh: function() {
		var me = this;
		
		me.suspendLayouts();
		
		// TODO: diff items and store instead of clear/repopulate?
		Ext.each(me.query(me.recordRowType), me.remove, me);
		me.getStore().each(me.addRow, me);
		
		me.resumeLayouts(true);
	}
	
	,addRow: function(record) {
		var me = this
			,row = me.add({
				xtype: me.recordRowType
				,itemId: record.id
			})
			,deleteCell = row.down('tabledeletecell');
			
		row.getForm().loadRecord(record);
		
		row.on({
			scope: me
			,expand: me.onChildExpandToggle
			,collapse: me.onChildExpandToggle
		});
		
		if(deleteCell)
		{
			deleteCell.on('click', me.onDeleteCellClick, me);
		}
		
		if(me.expandOnAdd && row.getForm().getRecord().phantom && row.isXType('tableexpando'))
		{
			row.on('afterrender', row.expand, row, {single: true, delay: 100});
		}
		
		return row;
	}
	
	/**
	 * Call .expand() on all rows, assuming they all implement .expand
	 */
	,expandAll: function() {
		Ext.each(this.query(this.recordRowType), function(row) {
			row.expand();
		});
	}
	
	/**
	 * Call .collapse() on all rows, assuming they all implement .collapse
	 */
	,collapseAll: function() {
		Ext.each(this.query(this.recordRowType), function(row) {
			row.collapse();
		});
	}
	
	/**
	 * Update all records in the store from their corresponding row fields
	 */
	,updateAllRecords: function() {
		Ext.each(this.query('[isFormable]'), function(formable) {
			formable.getForm().updateRecord();
		});
	}
	
	/**
	 * Attempts to update all records in the store from their corresponding row fields
	 * while pushing any validation errors from the model back to the field.
	 * @return {Object[]} An array of records that contained validation errors
	 * @return {Ext.data.Model} return.record
	 * @return {Ext.form.Basic} return.form
	 * @return {Ext.Container} return.formable
	 * @return {Ext.data.Errors} return.errors The errors object returned by {@link Ext.data.Model#validate}
	 */
	,syncAllRecords: function() {
		var storeErrors = []
			,form
			,record
			,recordErrors
			,expando;
			
		Ext.each(this.query('[isFormable]'), function(formable) {
			form = formable.getForm();
			record = form.getRecord();
			form.updateRecord();
			recordErrors = record.validate();
			form.clearInvalid();
			if(!recordErrors.isValid())
			{
				form.markInvalid(recordErrors);
				storeErrors.push({
					record: record
					,form: form
					,formable: formable
					,errors: recordErrors
				});
				
				// expand form any containing expando
				if(formable.isXType('tableexpando'))
				{
					formable.expand();
				}
			}
		});
		
		return storeErrors;
	}
	
	// @protected
	,getHeaderTarget: function() {
		return this.prev('dataheader');
	}
	
	// event handlers
	,onRecordAdd: function(store, records) {
		Ext.Array.each(records, this.addRow, this);
	}
	
	,onRecordRemove: function(store, record) {
		var toRemove = this.getComponent(record.id);
		
		// clear validation errors before removing field container
		Ext.each(toRemove.query('[isFormField]'), function(field) {
			field.clearInvalid();
		});
		
		this.remove(record.id);
	}
	
	,onDeleteCellClick: function(iconCell) {
		Ext.Msg.confirm('Delete row', 'Are you sure you want to delete this row?', function(btn) {
			if(btn == 'yes')
			{
				var record = iconCell.up('[isFormable]').getForm().getRecord();
				this.getStore().remove(record);
			}
		}, this);
	}
	
	,onChildExpandToggle: function(row) {
		var me = this
			,expanded = me.query('[expanded]').length
			,header = this.getHeaderTarget();

		if(expanded == 0)
		{
			if(header)
			{
				header.applyData('bulkOp', 'expand');
			}
			me.fireEvent('allcollapsed', me, row);
		}
		else if(expanded == me.getStore().getCount())
		{
			if(header)
			{
				header.applyData('bulkOp', 'collapse');
			}
			me.fireEvent('allexpanded', me, row);
		}
	}
});