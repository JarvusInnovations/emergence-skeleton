Ext.define('Jarvus.picker.Abstract', {
	extend: 'Ext.Sheet'
	,requires: [
		'Ext.Toolbar'
	]
	
	,config: {
		doneButton: true
		,cancelButton: true
		,value: null
		,centered: true
		,left: 0
		,right: 0
		,bottom: 0
		,toolbar: true
		,layout: 'fit'
	}
	
	,initElement: function() {
		this.callParent(arguments);
		
		this.on({
			scope: this
			,show: 'onShow'
		});
	}
	
	
	
	,applyToolbar: function(config) {
		if (config === true)
		{
			config = {};
		}
		
		Ext.applyIf(config, {
			docked: 'top'
		});
		
		return Ext.factory(config, 'Ext.TitleBar', this.getToolbar());
	}

	,updateToolbar: function(newToolbar, oldToolbar) {
		if (newToolbar)
		{
			this.add(newToolbar);
		}
		
		if (oldToolbar)
		{
			this.remove(oldToolbar);
		}
	}

	,applyDoneButton: function(config) {
		if (config)
		{
			if (Ext.isBoolean(config))
			{
				config = {};
			}
		
			if (typeof config == "string")
			{
				config = {
					text: config
				};
			}
		
			Ext.applyIf(config, {
				ui: 'action'
				,align: 'right'
				,text: 'Done'
			});
		}

		return Ext.factory(config, 'Ext.Button', this.getDoneButton());
	}
	
	,updateDoneButton: function(newDoneButton, oldDoneButton) {
		var toolbar = this.getToolbar();
		
		if (newDoneButton)
		{
			toolbar.add(newDoneButton);
			newDoneButton.on('tap', this.onDoneButtonTap, this);
		}
		else if (oldDoneButton)
		{
			toolbar.remove(oldDoneButton);
		}
	}

	,applyCancelButton: function(config) {
		if (config)
		{
			if (Ext.isBoolean(config))
			{
				config = {};
			}
		
			if (typeof config == "string")
			{
				config = {
					text: config
				};
			}
		
			Ext.applyIf(config, {
				align: 'left'
				,text: 'Cancel'
			});
		}
		
		return Ext.factory(config, 'Ext.Button', this.getCancelButton());
	}

	,updateCancelButton: function(newCancelButton, oldCancelButton) {
		var toolbar = this.getToolbar();
		
		if (newCancelButton)
		{
			toolbar.add(newCancelButton);
			newCancelButton.on('tap', this.onCancelButtonTap, this);
		}
		else if (oldCancelButton)
		{
			toolbar.remove(oldCancelButton);
		}
	}

	,onDoneButtonTap: function() {
		var oldValue = this._value,
		newValue = this.getValue(true);
		
		if (newValue != oldValue)
		{
			this.fireEvent('change', this, newValue);
		}
		
		this.hide();
	}

	,onCancelButtonTap: function() {
		this.fireEvent('cancel', this);
		this.hide();
	}
	
	
	,onShow: function() {
		if (!this.isHidden())
		{
			this.setValue(this._value);
		}
	}

	
	,setValue: function(value, animated) {
		this._value = value;
		
		return this;
	}

	,setValueAnimated: function(value) {
		this.setValue(value, true);
	}

	,getValue: function() {
		return this._value;
	}
	
	,getValues: function() {
		return this.getValue();
	}
});