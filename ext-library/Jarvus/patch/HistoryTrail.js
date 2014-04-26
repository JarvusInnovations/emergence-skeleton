/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Provides for keeping track of past tokens and intelligently using .back()
 * to return to the last token.
 */
Ext.define('Jarvus.patch.HistoryTrail', {
	override: 'Ext.util.History'
	
	// override History functions to record trail
	,startUp: function() {
		this.callParent(arguments);
		this.trail = [this.currentToken];
	}
	
	,handleStateChange: function(token) {
		
		if(this.trail[0] != token)
		{
			this.trail.unshift(token);	
		}
			
		this.callParent(arguments);
	}
	
	/**
	 * An alternative to Ext.History.add that should be used when the user triggers a navigation
	 * that could be symantically equivelent to clicking the browser's "back" button. 
	 * The token trail is checked to determine if the given URL was the last one loaded in the application;
	 * if it is, back() is executed, otherwise add(url) is.
	 */
	,backToUrl: function(url) {
		if(this.trail[1] == url)
		{
			this.trail.shift();
			Ext.History.back();
		}
		else
			Ext.History.add(url);
	}
});