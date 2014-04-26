/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A raw field for select-based dropdown menus
 */
Ext.define('Jarvus.field.Select', {
	extend: 'Jarvus.field.Field'
	,xtype: 'selectfield'
	
	/**
	 * @cfg {Object[]} options (required)
	 * A list of options that will populate the select field
	 * @cfg {string} options.label (required)
	 * @cfg {string} options.value (required)
	 */
	,options: null
	
	/**
	 * @cfg {String}
	 * A class to apply to the select tag
	 */
	,selectCls: null
	
	/**
	 * @cfg {Boolean}
	 * True to monitor the field for changes, updating the field value and firing the change event
	 * automatically when a selection is made
	 */
	,monitorChange: true
	
	/**
	 * @cfg {String}
	 * A label to show as the selected value when the field is disabled
	 */
	,disabledPlaceholder: false
	
	,maskOnDisable: false
	,renderTpl: [
		'<tpl if="label"><label><span>{label}</span></tpl>'
		,'<select id="{id}-selectEl"<tpl if="selectCls"> class="{selectCls}"</tpl><tpl if="options.length==1"> disabled="DISABLED"</tpl>>'
			,'<tpl for="options"><option value="{value}"<tpl if="String(value)===String(parent.value)"> selected="selected"</tpl>>{label}</option></tpl>'
		,'</select>'
		,'<tpl if="label"></label></tpl>'
	]
	,childEls: ['selectEl']
	
	
	,addChangeListener: function() {
		var me = this;
		
		me.mon(me.selectEl, 'change', function() {
			me.setValue(me.getValue());
		});
	}
	
	// component template methods
	,initRenderData: function() {
		var me = this;
		return Ext.applyIf(me.callParent(), {
			selectCls: me.selectCls
			,options: me.options
		});
	}
	
	,initEvents: function() {
		var me = this;
		
		if(me.monitorChange)
		{
			me.addChangeListener();
		}
		
		me.callParent(arguments);
	}
	
	,getFocusEl: function() {
		return this.selectEl;
	}
	
	,onDisable: function() {
		var me = this;
		
		me.callParent();
		
		if(me.disabledPlaceholder && !me.disabledOption)
		{
			me.suspendCheckChange++;
			me.disabledIndex = me.selectEl.dom.selectedIndex;
			me.disabledOption = me.selectEl.createChild({
				tag: 'option'
				,value: ''
				,html: me.disabledPlaceholder
				,selected: true
			});
		}
		
		me.selectEl.dom.disabled = true;
	}
	
	,onEnable: function() {
		var me = this;
		
		me.callParent();
		
		if(me.disabledOption)
		{
			me.selectEl.dom.selectedIndex = me.disabledIndex;
			me.disabledOption.destroy();
			me.disabledOption = null;
			me.suspendCheckChange--;
		}
		
		me.selectEl.dom.disabled = false;
	}
	
	
	// field template methods
	
	/**
	 * Get the currently selected value
	 */
	,getValue: function() {
		var me = this
			,s;
		
		if(me.selectEl)
		{
			s = me.selectEl.dom;
			me.value = s.options[s.selectedIndex].value;
		}
		
		return me.value;
	}
	
	/**
	 * Set the currently selected value. Must match one of the already defined options.
	 */
	,setValue: function(value) {
		var me = this
			,options, len, i = 0;
		
		if(me.selectEl)
		{
			options = me.selectEl.dom.options;
			len = options.length;
			
			for(;i < len; i++)
			{
				if(options[i].value == value)
				{
					if(me.disabledOption)
					{
						me.disabledIndex = i;
					}
					else
					{
						options[i].selected = true;
					}
					break;
				}
			}
		}
		
		return me.mixins.fieldable.setValue.call(me, value);
	}
	
	
	// selectfield methods
	
	/**
	 * Get the label of the currently selected value
	 * @return {String}
	 */
	,getValueLabel: function() {
		var s = this.selectEl.dom;
		return s.options[s.selectedIndex].innerHTML;
	}
});