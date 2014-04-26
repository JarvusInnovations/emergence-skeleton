/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Enhanced model validation so that validators can be made conditional on the value of another field
 */
Ext.define('Jarvus.patch.ConditionalValidation', {
	override: 'Ext.data.Model'
	
	/**
	 * @cfg {Object} checkIf
	 * A list of properties to check and what value they should match.
	 * All provided conditions must be true for the validator to be used.
	 */
	
	/**
	 * adds support for a checkIf option in validators that cause them to be ignored unless the record matches some conditions
	 */
	,validate: function() {
		var me = this;
		
		if(!Ext.isDefined(me.allValidations))
		{
			me.allValidations = me.validations;
		}
		
		me.validations = Ext.Array.filter(me.allValidations, function(validator) {
			var match = true;
			
			if(validator.checkIf)
			{
				Ext.Object.each(validator.checkIf, function(fieldName, value) {
					if(me.get(fieldName) != value)
					{
						match = false;
						return false;
					}
				});
			}
			
			return match;
		});
		
		return me.callParent(arguments);
	}
});