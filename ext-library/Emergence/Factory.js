Ext.define('Emergence.Factory', {
	config: {}
	,xtype: 'Emergence.Factory'
	,constructor: function(config) {
		this.initConfig(config);
		
		//Make a model name	
		this.ModelName = Ext.id(null,this.config.endpoint);
		
		// init store
		var store = this.createStore();

		return this;
	}
	,defineModel: function() {
	
		//this.config.modelFields.unshift({name: 'ID', type: 'integer'});
	
		Ext.define(this.ModelName, {
		    extend: 'Ext.data.Model'
		    ,fields: this.config.modelFields
		    ,validations: this.config.modelValidations
	        ,idProperty: 'ID'
		});	
	}
	,createStore: function() {
		if(this.store == null) {
			this.defineModel();
		
	    	var store = Ext.create('Ext.data.JsonStore',{
		        autoSync: true
		        ,autoLoad: true
		        ,sorters: this.config.sorters
		        ,model: this.ModelName
		        ,proxy: {
		            type: 'rest'
		            ,url: '/' + this.config.endpoint + '/json'
/*
					type: 'ajax'
					,api: {
						read: '/' + this.config.endpoint + '/json'
						,create: '/' + this.config.endpoint + '/json/save'
						,update: '/' + this.config.endpoint + '/json/save'
						,destroy: '/' + this.config.endpoint + '/json/destroy'
					}
*/
		            ,reader: {
		                type: 'json'
		                ,root: 'data'
		               	,successProperty: 'success'
                		,messageProperty: 'message'
		            }
/*
		            ,writer: {
		                type: 'json'
		                ,root: 'data'
		           		,successProperty: 'success'
                		,messageProperty: 'message'
		            }
*/
		            ,listeners: {
		                exception: function(proxy, response, operation){
		                    Ext.MessageBox.show({
		                        title: 'REMOTE EXCEPTION',
		                        msg: operation.getError(),
		                        icon: Ext.MessageBox.ERROR,
		                        buttons: Ext.Msg.OK
		                    });
		                }
		            }
		        }
		    });
		    
		    this.store = store;
	    }
	    
	    return this.store;
	}
	,createGrid: function() {
		var factory = this;

		var cellEditing = new Ext.grid.plugin.CellEditing();
		var rowEditing = new Ext.grid.plugin.RowEditing();


		this.grid = Ext.create('Ext.grid.Panel', {
	        plugins: [rowEditing,cellEditing]
	        ,title: this.config.title
	        ,store: this.store
	        ,iconCls: 'icon-user'
	        ,columns: this.config.gridHeader
	        ,viewConfig: {
	        	listeners: {mousewheel: true} //bug report: http://www.sencha.com/forum/showthread.php?131794-4.0.0-Ext.util.Observable-should-not-fail-on-removeListener-and-non-existent-event&highlight=mousewheel
	        }
	        ,dockedItems: [
	        {
	            xtype: 'toolbar',
	            items: [
		            {
		                text: 'Add',
		                iconCls: 'icon-add',
		                handler: function(){
		                    // empty record
		                    var record = Ext.create(this.ModelName);
		                    
		                    this.store.insert(0, record);
		                    rowEditing.startEdit(0, 0);
		                }
		            }
		            ,'-'
		            ,{
		                text: 'Delete',
		                iconCls: 'icon-delete',
		                handler: function(){
		                    var selection = grid.getView().getSelectionModel().getSelection();
		                    console.log('%o',selection);
		                }
		            }
	            ] // items
	        }
	        ] // dockedItems
	        ,bbar: new Ext.toolbar.Paging({
	            store: this.store,
	            displayInfo: true,
	            displayMsg: 'Displaying items {0} - {1} of {2}',
	            emptyMsg: "No items to display"
	        })
	    }); // var grid
	   
		return this.grid; 
	}
});