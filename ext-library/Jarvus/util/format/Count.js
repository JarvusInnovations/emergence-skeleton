/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.util.format.Count', {
	requires: 'Ext.util.Format'
	
	,count: function(n) {
		return Ext.isNumeric(n) ? n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '0';
	}
}, function(cls) {
	Ext.util.Format.count = cls.prototype.count;
});