Ext.define('Jarvus.field.DatePicker', {
	extend: 'Jarvus.field.Picker'
	,xtype: 'datepickerfield'
	,requires: [
		'Ext.Date'
		,'Ext.util.Format'
		,'Jarvus.picker.Date'
	]
	
	,pickerClass: 'Jarvus.picker.Date'
	
	,config: {
		dateFormat: null
	}
	
	,applyValue: function(value) {
		if (!Ext.isDate(value) && !Ext.isObject(value)) {
			return null;
		}
	
		if (Ext.isObject(value)) {
			return new Date(value.year, value.month - 1, value.day);
		}
	
		return value;
	}
	
	,formatDisplayValue: function(value) {
		return Ext.Date.format(value, this.getDateFormat() || Ext.util.Format.defaultDateFormat);
	}
	
	
	,updateDateFormat: function(newDateFormat, oldDateFormat) {
		var value = this.getValue();
		if (newDateFormat != oldDateFormat && Ext.isDate(value))
		{
			this.getComponent().setValue(Ext.Date.format(value, newDateFormat || Ext.util.Format.defaultDateFormat));
		}
	}
	
	,getFormattedValue: function(format) {
		var value = this.getValue();
		return (Ext.isDate(value)) ? Ext.Date.format(value, format || this.getDateFormat() || Ext.util.Format.defaultDateFormat) : value;
	}
});