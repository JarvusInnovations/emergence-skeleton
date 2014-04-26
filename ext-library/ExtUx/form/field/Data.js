/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,Slate*/
Ext.define('ExtUx.form.field.Data', {
	extend: 'Ext.form.FieldSet'
	,xtype: 'datafield'
	,requires: [
		'ExtUx.form.field.DataItem'	
	]
	
	,value: null
	
	,initComponent: function() {
		this.items = [{
			xtype: 'button'
			,text: 'Add Field'
			,action: 'add-data-node'
			,listeners: {
				click: this.onAddDataNode
				,scope: this
			}
		}];
		this.callParent(arguments);
	}
	
	,onAddDataNode: function() {
		var me = this
			,newField = me.down('fieldset[name=addfield]')
			,insertIndex = me.items.indexOfKey(me.items.getKey(me.down('button[action=add-data-node]')))
			,createWindow = null;
//		debugger;
		
		if(!newField){
			
			Ext.create('Ext.window.Window', {
				name: 'addfield'
				,title: 'New Node'
				,modal: true
				,items: [{
					xtype: 'textfield'
					,fieldLabel: 'Label'
					,flex: 1
					,name: 'Label'
					,allowBlank: false
				},{
					xtype: 'textfield'
					,fieldLabel: 'Value'
					,name: 'Value'
					,flex: 1
					,allowBlank: false
				},{
					xtype: 'container'
					,layout: 'hbox'
					,items: [{
						xtype: 'button'
						,text: 'Cancel'
						,handler: function(btn){
							btn.up('window').destroy();
						}
					},{
						xtype: 'button'
						,text: 'Submit'
						,handler: me.addDataNode
						,scope: me
					}]
				}]
			}).show();
			
//			me.insert(insertIndex, {
//				xtype: 'fieldset'
//				,name: 'addfield'
//				,layout: {
//					type: 'hbox'
//					,align: 'stretch'
//				}
//				,frame: false
//				,items: [{
//					xtype: 'textfield'
//					,emptyText: 'Label'
//					,flex: 1
//					,name: 'Label'
//					,allowBlank: false
//				},{
//					xtype: 'textfield'
//					,emptyText: 'Value'
//					,name: 'Value'
//					,flex: 1
//					,allowBlank: false
//				},{
//					xtype: 'button'
//					,text: 'Submit'
//					,handler: me.addDataNode
//					,scope: me
//					,width: 50
//				}]
//			});
		}
	}
	
	,addDataNode: function(btn) {
		var me = this
			,btnContainer = btn.up('container')
			,labelField = btnContainer.previousSibling('textfield[name=Label]')
			,label = labelField.getValue()
			,valueField = btnContainer.previousSibling('textfield[name=Value]')
			,nodeValue = valueField.getValue() 
			,keys = Ext.Object.getKeys(me.getValue())
			,insertIndex = me.items.indexOfKey(me.items.getKey(me.down('button[action=add-data-node]')))
			,createWindow = btn.up('window')
			,newNode;
		
		
		if(labelField.isValid() && valueField.isValid()) {
			if(Ext.Array.contains(keys, label)) {
				return Ext.Msg.alert('Duplicate Node', '"'+label+'" is already a in this field. Please change it to continue');
			}
			
			newNode = me.insert(insertIndex, {
				xtype: 'dataitemfield'
				,fieldLabel: label
				,name: label
				,value: nodeValue
			});
			
			me.syncValues();
			
			me.fireEvent('nodeadded', newNode);
			createWindow.destroy();
		}
	}
	
	,syncValues: function() {
		var me = this
			,fields = me.down('dataitemfield')
			,value = {};
			
		Ext.each(fields, function(field){
			value[field.name] = field.getValue();
		});
		
		me.setValue(value);
	}
	
	,setValue: function(value) {
		if(value == this.value && !Ext.isObject(value)) {
			return false;
		}

		this.value = value;
		this.updateValue(value);
	}
	
	,updateValue: function(newData) {
		var me = this
			,textfield;
			
		Ext.Object.each(newData, function(key, value){
			textfield = me.down('dataitemfield[name='+key+']');
			
			if(textfield){
				textfield.setValue(value);
			}
			else
			{
				me.insert(0,{
					xtype: 'dataitemfield'
					,fieldLabel: key
					,name: key
					,value: value
				});
			}
		});
	}
	
	,getValue: function(){
		var me = this
			,fields = me.query('dataitemfield')
			,value = {};
		
		Ext.each(fields, function(field){
			value[field.name] = field.getValue();
		});
		
		return value;
	}
});