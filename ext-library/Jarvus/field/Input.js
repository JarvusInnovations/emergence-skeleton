/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Base class for raw-fields that use a label-wrapped input tag
 */
Ext.define('Jarvus.field.Input', {
	extend: 'Jarvus.field.Field'
	,xtype: 'inputfield'
	,requires: [
		'Ext.form.field.Text'
	]

	/**
	 * @cfg {String}
	 * The type attribute for the input element
	 */
	,inputType: 'text'
	
	/**
	 * @cfg {String}
	 * A CSS class to be applied to the input element
	 */
	,inputCls: null
	
	/**
	 * @cfg {String}
	 * The pattern attribute for the input element
	 */
	,inputPattern: null
	
	/**
	 * @cfg {String}
	 * The placeholder attribute for the input element
	 */
	,inputPlaceholder: null
	
	/**
	 * @cfg {String}
	 * Additional attributes for the input element in the form _name="value" name2="value2"_
	 */
	,inputAttrs: null
	
	/**
	 * @cfg {Boolean/String}
	 * True to monitor for changes on blur, "live" to monitor on keyup, updating the field value and firing the change event
	 * automatically when a selection is made
	 */
	,monitorChange: true
	
	/**
	 * @cfg {Boolean}
	 * True to reset to the field's original value when the current value is completely erased
	 */
	,resetBlank: false
	
	/**
	 * @cfg {String}
	 * A value to display when the field is disabled
	 */
	,disabledPlaceholder: false
	
	/**
	 * @cfg {RegExp}
	 * A regular expression that will match against any character that should be allowed for input.
	 * Keypresess for characters that do not match this expression will be cancelled.
	 */
	,maskRe: false
	
	/**
	 * @cfg {Function}
	 * A custom function that can inspect keys as they are typed and cancel the event.
	 * 
	 * Receives the same parameters as {@link Ext.Element#keypress}, event and target.
	 */
	,filterKeys: false
	
	,maskOnDisable: false
	,renderTpl: [
		'<tpl if="label"><label><span>{label}</span></tpl>'
		,'<input'
			,' id="{id}-inputEl"'
			,' type="{inputType}"'
			,'<tpl if="inputPattern"> pattern="{inputPattern}"</tpl>'
			,'<tpl if="inputPlaceholder"> placeholder="{inputPlaceholder}"</tpl>'
			,'<tpl if="tip"> title="{tip}"</tpl>'
			,'<tpl if="inputCls"> class="{inputCls}"</tpl>'
			,'<tpl if="inputAttrs"> {inputAttrs}</tpl>'
			,'<tpl if="Ext.isDefined(value)">value="{[this.renderValue(values.$comp, values.value)]}"</tpl>'
		,'>'
		,'<tpl if="label"></label></tpl>'
		,{
			renderValue: function(field, value) {
				return field.transformRawValue(field.valueToRaw(value));
			}
		}
	]
	,childEls: ['inputEl']
	
	 
	,addChangeListener: function() {
		var me = this;
		
		me.mon(me.inputEl, me.monitorChange=='live'?'keyup':'blur', function(ev, t) {
			if(!me.suspendCheckChange)
			{
				var oldValue = me.value
					,newValue = me.getValue();
					
				if(newValue !== oldValue || me.rawDirty)
				{
					me.setValue(newValue);
				}
			}
		});
	}
	
	,onKeyPress: function() {
		var me = this;
		
		if(me.maskRe)
		{
			Ext.form.field.Text.prototype.filterKeys.apply(me, arguments);
		}
		
		if(me.filterKeys)
		{
			me.filterKeys.apply(me, arguments);
		}
	}
	
	// component template methods
	,initRenderData: function() {
		var me = this;
		return Ext.applyIf(me.callParent(), {
			inputType: me.inputType
			,inputCls: me.inputCls
			,inputPattern: me.inputPattern
			,inputPlaceholder: me.inputPlaceholder
			,inputAttrs: me.inputAttrs
		});
	}
	
	,initEvents: function() {
		var me = this;
		
		if(me.monitorChange)
		{
			me.addChangeListener();
		}

		if(me.maskRe || me.filterKeys)
		{
			me.mon(me.inputEl, 'keypress', me.onKeyPress, me);
		}
		
		me.callParent();
	}
	
	,getFocusEl: function() {
		return this.inputEl;
	}
	
	,onDisable: function() {
		var me = this;
		
		me.callParent();
		
		if(me.disabledPlaceholder && !me.disabledPlaceholderActive)
		{
			me.suspendCheckChange++;
			me.disabledPlaceholderActive = true;
			me.inputEl.dom.value = me.disabledPlaceholder;
		}
		
		me.inputEl.dom.disabled = true;
	}
	
	,onEnable: function() {
		var me = this;
		
		me.callParent();
		
		if(me.disabledPlaceholder && me.disabledPlaceholderActive)
		{
			me.inputEl.dom.value = me.rawValue;
			me.disabledPlaceholderActive = false;
			me.suspendCheckChange--;
		}
		
		me.inputEl.dom.disabled = false;
	}

//    ,onBlur: function() {
//		this.setValue(this.getValue());
//    }
	
	
	// field template methods
	// The following methods were lifted from Ext.form.field.Base
	// They implement a basic form interface with no labeling/layout dependencies
	
	/**
	 * Returns the raw value of the field, without performing any normalization, conversion, or validation. To get a
	 * normalized and converted value see {@link #getValue}.
	 * @return {String} value The raw String value of the field
	 */
	,getRawValue: function() {
		var me = this
			,v = (me.inputEl ? me.inputEl.getValue() : Ext.value(me.rawValue, ''));

		me.rawValue = v;
		return v;
	}
	
	/**
	 * Sets the field's raw value directly, bypassing {@link #valueToRaw value conversion}, change detection, and
	 * validation. To set the value with these additional inspections see {@link #setValue}.
	 * @param {Object} value The value to set
	 * @return {Object} value The field value that is set
	 */
	,setRawValue: function(value) {
		var me = this;
		value = Ext.value(me.transformRawValue(value), '');
		me.rawValue = value;

		// Some Field subclasses may not render an inputEl
		if (me.inputEl) {
			me.inputEl.dom.value = me.disabledPlaceholder && me.disabled ? me.disabledPlaceholder : value;
		}
		return value;
	}
	
	/**
	 * Transform the raw value before it is set
	 * @protected
	 * @param {Object} value The value
	 * @return {Object} The value to set
	 */
	,transformRawValue: function(value) {
		if(this.resetBlank && value === '')
		{
			return this.originalValue;
		}
		else
		{
			return value;
		}
	}
	
	/**
	 * Converts a mixed-type value to a raw representation suitable for displaying in the field. This allows controlling
	 * how value objects passed to {@link #setValue} are shown to the user, including localization. For instance, for a
	 * {@link Ext.form.field.Date}, this would control how a Date object passed to {@link #setValue} would be converted
	 * to a String for display in the field.
	 *
	 * See {@link #rawToValue} for the opposite conversion.
	 *
	 * The base implementation simply does a standard toString conversion, and converts {@link Ext#isEmpty empty values}
	 * to an empty string.
	 *
	 * @param {Object} value The mixed-type value to convert to the raw representation.
	 * @return {Object} The converted raw value.
	 */
	,valueToRaw: function(value) {
		return '' + Ext.value(value, '');
	}

	/**
	 * Converts a raw input field value into a mixed-type value that is suitable for this particular field type. This
	 * allows controlling the normalization and conversion of user-entered values into field-type-appropriate values,
	 * e.g. a Date object for {@link Ext.form.field.Date}, and is invoked by {@link #getValue}.
	 *
	 * It is up to individual implementations to decide how to handle raw values that cannot be successfully converted
	 * to the desired object type.
	 *
	 * See {@link #valueToRaw} for the opposite conversion.
	 *
	 * The base implementation does no conversion, returning the raw value untouched.
	 *
	 * @param {Object} rawValue
	 * @return {Object} The converted value.
	 */
	,rawToValue: function(rawValue) {
		return rawValue;
	}
	
	/**
	 * Performs any necessary manipulation of a raw field value to prepare it for {@link #rawToValue conversion} and/or
	 * {@link #validate validation}, for instance stripping out ignored characters. In the base implementation it does
	 * nothing; individual subclasses may override this as needed.
	 *
	 * @param {Object} value The unprocessed string value
	 * @return {Object} The processed string value
	 */
	,processRawValue: function(value) {
		return value;
	}
	
	/**
	 * Returns the current data value of the field. The type of value returned is particular to the type of the
	 * particular field (e.g. a Date object for {@link Ext.form.field.Date}), as the result of calling {@link #rawToValue} on
	 * the field's {@link #processRawValue processed} String value. To return the raw String value, see {@link #getRawValue}.
	 * @return {Object} value The field value
	 */
	,getValue: function() {
		var me = this;
		
		if(!me.disabled)
		{
			me.value = me.rawToValue(me.processRawValue(me.getRawValue()));
		}
		
		return me.value;
	}

	/**
	 * Sets a data value into the field and runs the change detection and validation. To set the value directly
	 * without these inspections see {@link #setRawValue}.
	 * @param {Object} value The value to set
	 * @return {Ext.form.field.Field} this
	 */
	,setValue: function(value) {
		var me = this;
		me.setRawValue(me.valueToRaw(value));
		return me.mixins.fieldable.setValue.call(me, value);
	}
	
});