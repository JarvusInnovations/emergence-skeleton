/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * This mixin facilitates updating template data by providing one or more changes to be
 * applied over the existing data.
 */
Ext.define('Jarvus.mixin.DataApplicator', {
	
	/**
	 * Applies one or more property values against currently loaded template data
	 * @param {String/Object} property The data property to be set, or an object of multiple properties
	 * @param {Mixed} [value] The value to apply to the given property, or null if an object was passed
	 * @return {Object} The updated data object
	 */
	applyData: function(property, value) {
		var me = this
			,data = me.data || {}
			,delta = property;
			
		if(!me.tpl)
		{
			return false;
		}
		
		if(!Ext.isObject(property) && Ext.isDefined(value))
		{
			delta = {};
			delta[property] = value;
		}
		
		Ext.apply(data, delta);
		me.update(data);
		return data;
	}
});