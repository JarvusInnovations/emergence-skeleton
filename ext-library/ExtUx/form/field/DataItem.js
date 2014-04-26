/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,Slate*/
Ext.define('ExtUx.form.field.DataItem', {
	extend: 'Ext.container.Container'
	,xtype: 'dataitemfield'

	,layout: {
		type: 'hbox'
		,align: 'stretch'
	}
	
	,initComponent: function(){
		var me = this;
		
		me.items = [{
			xtype: 'textfield'
			,name: me.name
			,fieldLabel: me.name
			,flex: 1
			,value: me.value
		},{
			xtype: 'component'
			,itemId: 'deleteCmp'
			,html: '<span class="delete-item">&times;</span>'
		}];
		
		me.callParent(arguments);
		
		me.down('#deleteCmp').on('afterrender', function(){
			me.down('#deleteCmp').el.on('click', me.onNodeDelete, me);
		}, me, {single: true});
	}
	

	,setValue: function(newValue) {
		return this.down('textfield').setValue(newValue);
	}
	
	,getValue: function() {
		return this.down('textfield').getValue();
	}
	
	,onNodeDelete: function() {
		var me = this
			,datafield;
		
		Ext.Msg.confirm(('Deleting '+me.name), 'Are you sure you want to delete this node? You cannot undo this action.', function(btn){
			if(btn == 'yes') {
				datafield = me.up('datafield');
				
				me.destroy();
				datafield.syncValues();
				
				datafield.fireEvent('nodedeleted', me);
			}
		});
	}
});