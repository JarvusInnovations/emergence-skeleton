/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A subclass of Ext.Mask that uses component hide/show instead of element hide/show so that
 * showAnimation/hideAnimation configuration works as expected.
 */
Ext.define('Jarvus.ux.AnimatedMask', {
	extend: 'Ext.Mask'
	,xtype: 'animatedmask'
	
	,config: {
		showAnimation: 'fadeIn'
		,hideAnimation: {
			type: 'fadeOut'
			,duration: 100
		}
	}
	
	/**
	 * Override {@link Ext.Component#method-doSetHidden} to use component hide/show methods
	 */
	,setHidden: function(hidden) {
		if(this.animatingMask) {
			this.animatingMask = false;
			this.callParent(arguments);
			return;
		}
		
		this.animatingMask = true;
		if (hidden) {
			this.hide();
		}
		else {
			this.show();
		}
	}
});