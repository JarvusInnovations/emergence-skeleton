Ext.define('Jarvus.field.Picker', {
	extend: 'Ext.field.Text'
	,xtype: 'pickerfield'
	,requires: [
		'Jarvus.picker.Abstract'
	]
	
	,isPicker: true
	,pickerClass: 'Jarvus.picker.Abstract'
	
	,config: {
		ui: 'select'
		,picker: true
		,clearIcon: false
		,destroyPickerOnHide: false
		,component: {
			useMask: true
		}
	}
	
	,initialize: function() {
		this.callParent();
		
		this.getComponent().on({
			scope: this
			,masktap: 'onMaskTap'
		});
		
		this.getComponent().input.dom.disabled = true;
	}
	
	,syncEmptyCls: Ext.emptyFn
	
	,updateValue: function(newValue) {
		var picker = this._picker;
		
		if (picker && picker.isPicker)
		{
			picker.setValue(newValue);
		}
	
		// Ext.Date.format expects a Date
		if (newValue !== null)
		{
			this.getComponent().setValue(this.formatDisplayValue(newValue));
		}
		else
		{
			this.getComponent().setValue('');
		}
	}
	
	,formatDisplayValue: function(value) {
		return value;
	}


	,getValue: function() {
		if (this._picker && this._picker.isPicker)
		{
			return this._picker.getValue();
		}
	
		return this._value;
	}

	,applyPicker: function(picker, pickerInstance) {
		
		if(picker === true)
			picker = {};
			
		Ext.applyIf(picker, {
			toolbar: {
				title: this.getLabel()
			}
		});
		
		if (pickerInstance && pickerInstance.isPicker)
		{
			picker = pickerInstance.setConfig(picker);
		}
		
		return picker;
	}

	,getPicker: function() {
		var picker = this._picker
			,value = this.getValue();

		if (picker && !picker.isPicker)
		{
			picker = Ext.factory(picker, this.pickerClass);
			if (value != null)
			{
				picker.setValue(value);
			}
		}
		
		picker.on({
			scope: this,
			change: 'onPickerChange',
			hide  : 'onPickerHide'
		});
		
		Ext.Viewport.add(picker);
		this._picker = picker;

		return picker;
    }


	,onMaskTap: function() {
		if (this.getDisabled())
		{
			return false;
		}
	
		if (this.getReadOnly())
		{
			return false;
		}
	
		this.getPicker().show();
	
		return false;
	}
	
	,onPickerChange: function(picker, value) {
		var me = this;
		
		me.setValue(value);
		me.fireEvent('change', me, me.getValue());
	}

	,onPickerHide: function() {
		var picker = this.getPicker();
		if (this.getDestroyPickerOnHide() && picker)
		{
			picker.destroy();
			this._picker = true;
		}
	}

	,reset: function() {
		this.setValue(this.originalValue);
	}

	,destroy: function() {
		var picker = this._picker;
		
		if (picker && picker.isPicker)
		{
			picker.destroy();
		}
		
		this.callParent(arguments);
	}
});