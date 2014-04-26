declare('Manager::RecordsStore', function(use) {
    use([], function(use) {
    

		Manager.RecordsStore = Ext.extend(Ext.data.Store, {
		
			sortInfo: {
				field: 'Created'
				,direction: 'DESC'
			}
			
			,isLoaded: false
			,singularNoun: 'record'
			,pluralNoun: 'records'
			,proxyConfig: null
			,fields: []
		
			,constructor: function(config) {
				config = config || {};
			
				// setup proxy
				if(!config.proxy)
				{
					if(!this.proxyConfig)
						throw('RecordsStore requires proxyConfig');
						
					config.proxy = new Ext.data.HttpProxy(this.proxyConfig);
				}
				
				// get fields
				this.fields = this.getFields();
				if(typeof this.fields != 'function')
					this.fields = this.fields.concat(config.fields || []);
				
				// setup reader
				if(!config.reader)
				{
					config.reader = new Ext.data.JsonReader({
						root: 'data'
						,idProperty: 'ID'
						,successProperty: 'success'
						,totalProperty: 'total'
						,messageProperty: 'message'
						,fields: this.fields
					});
				}

				// setup writer
				if(!config.writer)
				{
					config.writer = new Ext.data.JsonWriter({
						listful: true
						,encode: false
						//,writeAllFields: true
					});
				}
				
				// run parent
				Manager.RecordsStore.superclass.constructor.call(this, config);

				// setup load listener
				this.on('load', function() {
					this.isLoaded = true;
				});
		
			}
			
			
			,getFields: function () {
			
				return [{
					"type":"int",
					"name":"ID"
				},{
					"type":"string",
					"name":"Class"
				},{
					"type":"date",
					"dateFormat":"timestamp",
					"name":"Created",
					"sortType":"asDate"
				},{
					"type":"int",
					"name":"CreatorID"
				},{
					name: "Creator"
				}];
			
			}
			
		
		});
		
		
		
		// register xtype to allow for lazy initialization
		Ext.reg('RecordsStore', Manager.RecordsStore);

		
	}); //end use
}); //end declare
