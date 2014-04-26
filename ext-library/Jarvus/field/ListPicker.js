Ext.define('Jarvus.field.ListPicker', {
	extend: 'Jarvus.field.Picker'
	,xtype: 'listpickerfield'
	,requires: [
		'Jarvus.picker.List'
		,'Ext.data.Store'
		,'Ext.data.StoreManager'
	]
	
	,pickerClass: 'Jarvus.picker.List'
	
	,config: {
		valueField: 'value'
		,displayField: 'text'
		,store: true
	}
	
	
	,updateLabel: function(newLabel) {
		this.callParent(arguments);
		
		if(this._picker && this._picker.isPicker)
			this.getPicker().getToolbar().setTitle(newLabel);
	}
	
	,getPicker: function() {
		var picker = this.callParent(arguments)
			,list = picker.getList();
		
		if(this.record)
			list.select(this.record);
			
		return picker;
	}

	,applyPicker: function(picker, pickerInstance) {
		
		if(picker === true)
			picker = {};
			
		Ext.applyIf(picker, {
			list: {
				itemTpl: '<span class="x-list-label">{'+this.getDisplayField()+'}</span>'
				,store: this.getStore()
				,listeners: {
					scope: this
					,itemtap: 'onListItemTap'
					,select: 'onListItemSelect'
				}
			}
		});

		return this.callParent([picker, pickerInstance]);
	}
	
	,applyStore: function(store) {
		var displayField = this.getDisplayField();
		
		if (store === true)
		{
			store = Ext.create('Ext.data.Store', {
				fields: [this.getValueField(), this.getDisplayField()]
				,grouper: {
					groupFn: function(record) {
						return record.get(displayField).substr(0,1);
					}
					,sortProperty: displayField
				}
			});
		}
		
		if (store)
		{
			store = Ext.data.StoreManager.lookup(store);
			
			store.on({
				scope: this
				,addrecords: this.onStoreDataChanged
				,removerecords: this.onStoreDataChanged
				,updaterecord: this.onStoreDataChanged
				,refresh: this.onStoreDataChanged
			});
		}
		
		return store;
	}

	,updateStore: function(newStore) {
		if (newStore)
		{
			if(this._picker && this._picker.isPicker)
				this.getPicker().getList().setStore(newStore);
				
			this.onStoreDataChanged(newStore);
		}
	}
	
	,onStoreDataChanged: function(store) {
		var initialConfig = this.getInitialConfig()
			,value = this.getValue();
		
		if (Ext.isDefined(value))
		{
			this.updateValue(this.applyValue(value));
		}
		
		if (this.getValue() === null)
		{
			if (initialConfig.hasOwnProperty('value'))
			{
				this.setValue(initialConfig.value);
			}
			
			if (this.getValue() === null)
			{
				if (store.getCount() > 0)
				{
					this.setValue(store.getAt(0));
				}
			}
		}
	}
	
	,reset: function() {
		var store = this.getStore()
			,record = (this.originalValue) ? this.originalValue : store.getAt(0);
		
		if (store && record)
		{
			this.setValue(record);
		}
		
		return this;
	}
	
	,applyValue: function(value) {
		var record = value
			,store = this.getStore()
			,index;
	
		if ((value && !value.isModel) && store)
		{
			index = store.find(this.getValueField(), value, null, null, null, true);
		
			if (index == -1)
				index = store.find(this.getDisplayField(), value, null, null, null, true);
	
			record = store.getAt(index);
		}
		
		return record;
	}
	
	,updateValue: function(newValue, oldValue) {
		this.previousRecord = oldValue;
		this.record = newValue;
		
		if(this._picker && this._picker.isPicker)
			this._picker.getList().select(newValue);
		
		this.callParent([(newValue && newValue.isModel) ? newValue.get(this.getDisplayField()) : '']);
	}
	
	,getValue: function() {
		var record = this.record;
		return (record && record.isModel) ? record.get(this.getValueField()) : null;
	}
	
	
    ,onListItemTap: function() {
        this.getPicker().hide();
    }
    
    ,onListItemSelect: function(item, record) {
		if (record)
		{
			this.setValue(record);
		}
    }
});