/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Base class for creating rawfields that build on Ext.Component and implement the
 * {@link Jarvus.mixin.Fieldable fieldable interface} to work with Ext.form.Basic
 * while using minimal/custom markup.
 * @abstract
 */
Ext.define('Jarvus.field.Field', {
	extend: 'Ext.Component'
	,mixins: {
		fieldable: 'Jarvus.mixin.Fieldable'
	}
	,xtype: 'rawfield'
	
	/**
	 * @cfg {String} name
	 * Name that form-field will be submitted under or set in model by
	 */
	
	/**
	 * @cfg {String}
	 * Human-readable label for the field
	 */
	,label: null
	
	/**
	 * @cfg {String}
	 * An extended explanation for the field
	 */
	,tip: null
	
	/**
	 * @cfg {String}
	 * CSS class to be applied to the component when the field has focused
	 */
	,focusCls: 'form-focus'
	
	// component template methods
	,initComponent: function() {
		this.callParent(arguments);
		
		// init Field mixin
		this.initFieldable();
	}
	
	,initRenderData: function() {
		var me = this;
		return Ext.applyIf(me.callParent(), {
			label: me.label
			,tip: me.tip
			,value: me.value
		});
	}
});