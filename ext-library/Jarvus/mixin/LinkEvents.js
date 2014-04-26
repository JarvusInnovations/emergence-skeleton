/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * Component mixin that intercepts clicks on any anchor links
 * with a hash component and fires the click as a component event.
 * 
 * For example, a component containing the following markup:
 *     <a href="#delete">Delete this article</a>
 * 
 * Will fire an event called "deleteclick", passing a reference to this component followed by the
 * click event's {@link Ext.EventObject} and target parameters.
 */
Ext.define('Jarvus.mixin.LinkEvents', {
	
	/**
	 * Initialize the mixin, must be called by the mixed class's
	 * initComponent method
	 */
	initLinkEvents: function() {
		var me = this;
		me.mon(me.el, 'click', function(ev, t) {
			if(t.hash.length > 1)
			{
				ev.stopEvent();
				me.fireEvent(t.hash.substr(1)+'click', me, ev, t);
			}
		}, null, {delegate: 'a'});
	}
});