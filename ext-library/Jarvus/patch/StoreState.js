Ext.define('Jarvus.patch.StoreState', {
	override: 'Ext.data.Store'
	
	,constructor: function() {
		this.callParent(arguments);
		
		this.on('beforeload', function(store) {
			store.loading = true;
		});
		
		this.on('load', function(store) {
			store.loaded = true;
			store.loading = false;
		});
	}
	
	,loadData: function() {
		this.callParent(arguments);
		this.loaded = true;
	}
	
	/**
	 * Returns true if the Store is currently performing a load operation
	 * @return {Boolean} True if the Store is currently loading
	 */
	,isLoading: function() {
		return Boolean(this.loading);
	}

	/**
	 * Returns true if the Store has been loaded.
	 * @return {Boolean} True if the Store has been loaded
	 */
	,isLoaded: function() {
		return Boolean(this.loaded);
	}
	
});