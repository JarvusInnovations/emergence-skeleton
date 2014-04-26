/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A rawfield for checkbox and radio inputs
 */
Ext.define('Jarvus.field.Boolean', {
	extend: 'Jarvus.field.Input'
	,xtype: 'booleanfield'
	
	/**
	 * @cfg {String}
	 * If set, input will be a radio instead of checkbox and name will be set to this value
	 */
	,radioGroup: null
	
	,componentCls: 'field-boolean'
	,renderTpl: '<input id="{id}-inputEl" <tpl if="radioGroup"> type="radio" name="{radioGroup}"<tpl else> type="checkbox"</tpl><tpl if="value"> checked="checked"</tpl>>'
	
	
	,addChangeListener: function() {
		var me = this;
		
		me.mon(me.inputEl, 'click', function() {
			if(me.radioGroup)
			{
				Ext.each(me.up('[isFormable]').query('booleanfield[radioGroup='+me.radioGroup+']'), function(radioField) {
					radioField.setValue(radioField.getValue());
				});
			}
			else
			{
				me.setValue(me.getValue());
			}
		});
	}
	
	// component template methods
	,initRenderData: function() {
		var me = this;
		return Ext.applyIf(me.callParent(), {
			radioGroup: me.radioGroup
		});
	}
	
	// field template methods
	,onChange: function(value) {
		this.inputEl.dom.checked = !!value;
	}
	
	,valueToRaw: function(value) {
		return !!value;
	}

	,getRawValue: function() {
		this.rawValue = Ext.value(this.inputEl.dom.checked, false);
		return this.rawValue;
	}
	
	,setRawValue: function(value) {
		var me = this;
		value = Ext.value(value, false);
		me.rawValue = value;
		
		if(me.inputEl)
		{
			me.inputEl.dom.checked = value;
		}
		return value;
	}
});