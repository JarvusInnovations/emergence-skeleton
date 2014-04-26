/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Adds integer and float validation types
 */
Ext.define('Jarvus.validation.Number', {
	override: 'Ext.data.validations'
	
	,integerRe: /^-?\d+$/
	,integerMessage: 'must be an integer number'
	
	,floatRe: /^-?(\.\d+|\d+\.?\d*)$/
	,floatMessage: 'must be a number'
	
	
	,'integer': function(config, value) {
		
		// convert type
		if(!Ext.isNumber(value))
		{
			if(value === null || value === undefined)
				return !!config.allowNull;
			else if(this.integerRe.test(value))
				value = parseInt(value, 10);
			else
				return false;
		}
		else if(value%1 !== 0)
		{
			return false;
		}
		
		// enforce min/max
		if(Ext.isNumber(config.min) && value < config.min)
			return false;
		
		if(Ext.isNumber(config.max) && value > config.max)
			return false;
			
		return true;
	}

	,'float': function(config, value) {
		
		// convert type
		if(!Ext.isNumber(value))
		{
			if(value === null || value === undefined)
				return !!config.allowNull;
			else if(this.floatRe.test(value))
				value = parseFloat(value);
			else
				return false;
		}
		
		// enforce min/max
		if(Ext.isNumber(config.min) && value < config.min)
			return false;
		
		if(Ext.isNumber(config.max) && value > config.max)
			return false;
			
		return true;
	}
});