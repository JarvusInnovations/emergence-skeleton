/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.patch.SoftLocalStorage', {
	override: 'Ext.state.LocalStorageProvider'
	
	// stop LocalStorageProvider from generating a hard error in IE6
	// see http://www.sencha.com/forum/showthread.php?220574-4.1.x-Instantiating-local-storage-provider-in-old-IE-causes-hard-error
	,constructor: function() {
        var me = this;
        Ext.state.Provider.prototype.constructor.apply(me, arguments);
        me.store = me.getStorageObject();
        me.state = me.store ? me.readLocalStorage() : false;
	}
});