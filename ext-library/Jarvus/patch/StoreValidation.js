/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.patch.StoreValidation', {
	override: 'Ext.data.Store'
	
	,validateAll: function() {
		var errors = []
			,recordErrors;
		
		this.each(function(record) {
			recordErrors = record.validate();
			if(!recordErrors.isValid())
			{
				errors.push({
					record: record
					,errors: recordErrors
				});
			}
		});
		
		return errors;
	}
});