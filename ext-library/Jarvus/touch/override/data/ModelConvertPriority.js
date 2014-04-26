/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.touch.override.data.ModelConvertPriority', {
	override: 'Ext.data.Model'
	
	,sortConvertFields: function(field1, field2) {
		var f1SpecialConvert = field1.hasCustomConvert()
			,f1ConvertPriority = field1.config.convertPriority
			,f2SpecialConvert = field2.hasCustomConvert()
			,f2ConvertPriority = field2.config.convertPriority;
			
		if (f1SpecialConvert && !f2SpecialConvert) {
			return 1;
		}
		if (!f1SpecialConvert && f2SpecialConvert) {
			return -1;
		}
		if (!f1SpecialConvert && !f2SpecialConvert) {
			return 0;
		}
		
		// tie breaker - convertPriority
		if ((!f1ConvertPriority && f2ConvertPriority) || (f1ConvertPriority > f2ConvertPriority)) {
			return 1;
		}
		if ((f1ConvertPriority && !f2ConvertPriority) || (f1ConvertPriority < f2ConvertPriority)) {
			return -1;
		}
		
		return 0;
	}
});