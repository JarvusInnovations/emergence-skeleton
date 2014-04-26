/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A subclass of Ext's standard interface mixin for form fields, Ext.form.field.Field, that provides basic
 * validation management like the abstract field class Ext.form.field.Base but without
 * all the markup and layout bloat that comes with the Ext.form.Labelable mixin that field.Base uses
 */
Ext.define('Jarvus.mixin.Fieldable', {
	extend: 'Ext.form.field.Field'
	
	/**
	 * Initialize the mixin, must be called by the mixed class's
	 * initComponent method
	 */
	,initFieldable: function() {
		this.initField();
	}
	
	/**
	 * Mark the field as invalid with the supplied error message
	 * @param {String} msg The human-readable error message
	 */
	,markInvalid: function(msg) {
		var me = this;
		
		if(me._error !== msg)
		{
			me._error = msg;
			me.fireEvent('validitychange', me, false, msg);
			me.addCls('field-invalid');
			if (me.el) {
				me.el.set({
					'data-qtip': msg
					,'data-qclass': 'invalid'
				});
			}
		}
	}
	
	/**
	 * Remove any stored error message and mark the field as valid
	 */
	,clearInvalid: function() {
		var me = this;
		
		if(me._error)
		{
			me._error = null;
			me.fireEvent('validitychange', me, true);
			me.removeCls('field-invalid');
			if (me.el) {
				me.el.set({
					'data-qtip': ''
					,'data-qclass': ''
				});
			}
		}
	}
	
	/**
	 * Get the currently set validation error
	 * @return {String} The last-set error message
	 */
	,getError: function() {
		return this._error;
	}
});