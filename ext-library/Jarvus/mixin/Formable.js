/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Turns any container into a manageable form
 */
Ext.define('Jarvus.mixin.Formable', {
	extend: 'Ext.form.FieldAncestor'
	,requires: [
		'Ext.form.Basic'
	]
	
	/**
	 * @cfg {Boolean}
	 * True to clear validation errors automatically when a field is edited
	 */
	,clearInvalidOnChange: true
	
	/**
	 * @cfg {Boolean}
	 * True to capture tab presses and guide the keyboard cursor between invalid fields
	 */
	,tabNextInvalid: true
	
	/**
	 * @cfg {Object}
	 * Configuration for the underlying {@link Ext.form.Basic}
	 */
	,formConfig: {
		trackResetOnLoad: true
	}

	,isFormable: true
	
	
	/**
	 * Get the {@link Ext.form.Basic} instance needed to collectively manage the container's
	 * form fields. This accessor must be used as the form instance will be created on-the-fly
	 * when it is first needed.
	 * @return {Ext.form.Basic}
	 */
	,getForm: function() {
		var me = this;
		
		// initialize form on-the-fly
		if(!me.form)
		{
			me.initFieldAncestor();
			// FieldAncestor is typically run during FormPanel's constructor, but we lazy-init
			// the form to keep views clear of boilerplate constructors. The add/remove listeners
			// initFieldAncestor sets up will catch any future changes, but we need to manually
			// add any existing components since the Formable view may have already been rendered.
			Ext.each(me.query('[isFormField]'), me.onFieldAdded, me);
			
			me.form = Ext.create('Ext.form.Basic', me, me.formConfig);
		}
		return me.form;
	}
	
	/**
	 * Shortcut to {@link Ext.form.Basic#loadRecord}
	 */
	,loadRecord: function(record) {
		this.getForm().loadRecord(record);
	}

	/**
	 * Shortcut to {@link Ext.form.Basic#getRecord}
	 */
	,getRecord: function() {
		return this.getForm().getRecord();
	}
	
	// @private
	,onFieldAdded: function(field) {
		var me = this;
		
		me.callParent(arguments);
		
		if(me.clearInvalidOnChange)
		{
			field.on('change', function() {
				field.clearInvalid();
			});
		}
		
		// Unfinished feature to guide tabs between invalid fields
		if(me.tabNextInvalid)
		{
			if(field.rendered)
			{
				me.attachKeyListener(field);
			}
			else
			{
				field.on('afterrender', function() {
					me.attachKeyListener(field);
				}, null, {single: true})
			}
		}
	}
	
	// @private
	,attachKeyListener: function(field) {
		var me = this
			,focusEl = field.getFocusEl();
		
		if(focusEl)
		{
			field.mon(focusEl, 'keydown', function(ev, t) {
				if(ev.keyCode == ev.TAB && field.getError())
				{
					// find all visible fields with errors
					var errorEls = Ext.ComponentQuery.query('[rendered][isFormField][getError]{getError()}{up("[hidden]")==undefined}')
						,fieldIndex = Ext.Array.indexOf(errorEls, field)
						,nextField;

					if(!errorEls || (errorEls.length == 1 && errorEls[0] === field))
					{
						return;
					}
					
					ev.stopEvent();
					
					if(fieldIndex == -1)
					{
						nextField = errorEls[0];
					}
					else if(ev.shiftKey)
					{
						// tab to previous or last error
						nextField = errorEls[fieldIndex > 0 ? fieldIndex-1 : errorEls.length-1];
					}
					else
					{
						// tab to next or first error
						nextField = errorEls[fieldIndex+1 < errorEls.length ? fieldIndex+1 : 0];
					}
					
					nextField.focus();
				}
			});
		}
	}
});