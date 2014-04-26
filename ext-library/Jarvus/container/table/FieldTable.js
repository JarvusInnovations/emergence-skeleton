/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A container for table segments that implements the {@link Ext.form.FieldAncestor} mixin
 * for interacting with all fields contained within, regardless of nesting depth.
 * 
 * If the child items contain a {@link Jarvus.container.table.Header tableheader}, it will
 * automatically receive the first error from a contained field any time there is a validity change.
 * 
 * Creates a table tag.
 */
Ext.define('Jarvus.container.table.FieldTable', {
	extend: 'Jarvus.container.Raw'
	,xtype: 'fieldtable'
	,mixins: {
		fieldAncestor: 'Ext.form.FieldAncestor'
	}
	,requires: [
		'Jarvus.container.table.Segment'
		,'Ext.util.MixedCollection'
	]
	
	,autoEl: 'table'
	,componentCls: 'datatable'
	,defaultType: 'tablesegment'
	
	// Component template methods
	,initComponent: function() {
		var me = this;
		
		me._errorFields = Ext.create('Ext.util.MixedCollection');
		
		// create a small buffer so that the header only updates once per wave of field changes
		me.syncErrorHeader = Ext.Function.createBuffered(me.syncErrorHeader, 10, me);
		
		me.initFieldAncestor();
		me.callParent();
	}
	
	// FieldAncestor template methods
	,onFieldValidityChange: function(field, isValid) {
		var ef = this._errorFields;

		// keep list of descendant fields with error
		if(isValid && ef.contains(field))
		{
			ef.remove(field);
		}
		else if(!isValid && !ef.contains(field))
		{
			ef.add(field);
		}
		
		this.syncErrorHeader();
	}
	
	// private methods
	,syncErrorHeader: function() {
		var me = this
			,tableHeader = me.down('>tableheader')
			,firstErrorField = me._errorFields.first();
			
		if(tableHeader)
		{
			if(firstErrorField)
			{
				if(me._highlightedField !== firstErrorField)
				{
					if(me._highlightedField && !me._highlightedField.isDestroyed)
					{
						me._highlightedField.removeCls('field-highlighted');
					}
					tableHeader.setError(firstErrorField.getError());
					me._highlightedField = firstErrorField;
					me._highlightedField.addCls('field-highlighted');
				}
			}
			else if(me._highlightedField)
			{
				tableHeader.clearError();
				if(!me._highlightedField.isDestroyed)
				{
					me._highlightedField.removeCls('field-highlighted');
				}
				me._highlightedField = null;
			}
		}
	}
});