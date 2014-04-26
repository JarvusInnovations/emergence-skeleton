/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A convenient subclass of {@link Ext.Container} that defaults to a
 * {@link Jarvus.layout.container.Raw raw layout}.
 */
Ext.define('Jarvus.container.Raw', {
	extend: 'Ext.Container'
	,xtype: 'rawcontainer'
	,requires: [
		'Jarvus.layout.container.Raw'
	]

	,layout: 'raw'
});