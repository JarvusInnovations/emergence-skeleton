Ext.define('Emergence.Site.People', {
	extend: 'Emergence.Factory'
	,alias: 'widget.people'
	,require: 'Ext.window.Window'
	,constructor: function() {
	
		this.accountLevelDropdown = Ext.create('Ext.data.Store', {
		    fields: ['value', 'name'],
		    data : [
		        {"value":"Disabled", "name":"Disabled"}
		        ,{"value":"Contact", "name":"Contact"}
		        ,{"value":"User", "name":"User"}
		        ,{"value":"Staff", "name":"Staff"}
		        ,{"value":"Administrator", "name":"Administrator"}
		        ,{"value":"Developer", "name":"Developer"}
		    ]
		});
	
		var config = [{
			endpoint: 'people' 
			,title: 'People'
			,modelFields: [
		    	, 'ID'
		    	, 'Email'
		    	, 'FirstName'
		    	, 'LastName'
		    	, 'AccountLevel'
		    	, 'Username'
		    ]
		    ,modelValidation: [
			    {
			        type: 'length',
			        field: 'Email',
			        min: 1
			    }, {
			        type: 'length',
			        field: 'FirstName',
			        min: 1
			    }, {
			        type: 'length',
			        field: 'LastName',
			        min: 1
			    }, {
			        type: 'length',
			        field: 'LastName',
			        min: 1
			    }
		    ]
		    ,gridHeader: [
		        {
		            text: 'ID',
		            width: 40,
		            sortable: true,
		            dataIndex: 'ID'
		        }, {
		            text: 'Email',
		            flex: 1,
		            sortable: true,
		            dataIndex: 'Email',
		            field: {
		                xtype: 'textfield'
		            }
		        }, {
		            header: 'First Name',
		            width: 80,
		            sortable: true,
		            dataIndex: 'FirstName',
		            field: {
		                xtype: 'textfield'
		            }
		        }, {
		            text: 'Last Name',
		            width: 80,
		            sortable: true,
		            dataIndex: 'LastName',
		            field: {
		                xtype: 'textfield'
		            }
		        }
		        , {
		            text: 'Account Level',
		            width: 80,
		            sortable: true,
		            dataIndex: 'AccountLevel',
		            field: {
		                xtype: 'combobox'
		                ,store: Ext.create('Ext.data.Store', {
						    fields: ['value', 'name'],
						    data : [
						        {"value":"Disabled", "name":"Disabled"}
						        ,{"value":"Contact", "name":"Contact"}
						        ,{"value":"User", "name":"User"}
						        ,{"value":"Staff", "name":"Staff"}
						        ,{"value":"Administrator", "name":"Administrator"}
						        ,{"value":"Developer", "name":"Developer"}
						    ]
						})				
						,displayField: 'name'
					    ,valueField: 'value'
		            }
		        }
			]
		}];
		return this.callParent(config);
	}
	,handleAddUser: function() {
		//var factory = this;
		
		var gridPanel = this.up().up();
		
		gridPanel.addUserWindow = Ext.create('Ext.window.Window',{
			title: 'Add a User'
			,layout: 'fit'
			,items: {
				xtype: 'form'
				,url: '/users/json/createUser'
				,layout: 'anchor'
				,defaults: {
					anchor: '100%'
				}
				,defaultType: 'textfield'
				,items: [
					{
						fieldLabel: 'First Name'
						,name: 'FirstName'
						,allowBlank: false
					}
					,{
						fieldLabel: 'Last Name'
						,name: 'LastName'
						,allowBlank: false
					}
					,{
						fieldLabel: 'Email'
						,name: 'Email'
						,allowBlank: false
					}
					,{
						fieldLabel: 'Username'
						,name: 'Username'
						,allowBlank: false
					}
					,{
						fieldLabel: 'Password'
						,name: 'Password'
						,inputType: 'password'
						,allowBlank: false
					}
					,{
						inputType: 'hidden'
						,name: 'Class'
						,value: 'User'
					}
					,{
				      	xtype: 'combobox'
		                ,fieldLabel: 'Account Level'
		                ,name: 'AccountLevel'
		                ,store: Ext.create('Ext.data.Store', {
						    fields: ['value', 'name'],
						    data : [
						        {"value":"Disabled", "name":"Disabled"}
						        ,{"value":"Contact", "name":"Contact"}
						        ,{"value":"User", "name":"User"}
						        ,{"value":"Staff", "name":"Staff"}
						        ,{"value":"Administrator", "name":"Administrator"}
						        ,{"value":"Developer", "name":"Developer"}
						    ]
						})
					    ,displayField: 'name'
					    ,valueField: 'value'
		            }
				]
				,buttons: [
					{
						text: 'Reset'
						,handler: function() {
							this.up('form').getForm().reset();
						}
					}
					,{
						text: 'Submit'
						,handler: function() {
							var form = this.up('form').getForm();
							
							if(form.isValid()) {
								form.submit({
									success: function(form, action) {
				                       //Ext.Msg.alert('Success', action.result.msg);
				                       gridPanel.addUserWindow.close();
				                       gridPanel.store.load();
				                    },
				                    failure: function(form, action) {
				                    	
										//console.log(action);
				                    	
								        if(action.result.success)
								        {
								        	
								        }
								        else {
								        	var error = '';
								     
								        	for(var item in action.result.data.validationErrors) {
								        		if(action.result.data.validationErrors.hasOwnProperty(item))
								        		{
								        			error += action.result.data.validationErrors[item] + "\n";
								        		}
								        		
								        	}
								        	Ext.Msg.alert('Failed', error);
								        }
				                    }
								});
							}
						}
					}
				]
			}
		});
		
		gridPanel.addUserWindow.show();
	}
	,createGrid: function() {
		var factory = this;

		var cellEditing = new Ext.grid.plugin.CellEditing();
		var rowEditing = new Ext.grid.plugin.RowEditing();
	   
	   	this.gridForm = Ext.create('Ext.form.Panel', {
			title: this.config.title
			,iconCls: 'icon-user'
			,bodyPadding: 5
			,url: '/people/json/save'
			,width: 750
			,layout: 'border'
			,fieldDefaults: {
				labelAlign: 'left'
				,msgTarget: 'side'
			}
			,items: [{
		            columnWidth: 0.60
		            ,xtype: 'gridpanel'
		            ,plugins: [rowEditing,cellEditing]
			        ,store: this.store
			        ,columns: this.config.gridHeader
			        ,region: 'center'
			        ,listeners: {
		                selectionchange: function(model, records) {
		                    if (records[0]) {
		                        this.up('form').getForm().loadRecord(records[0]);
		                    }
		                }
		            }
			        ,viewConfig: {
			        	listeners: {mousewheel: true} //bug report: http://www.sencha.com/forum/showthread.php?131794-4.0.0-Ext.util.Observable-should-not-fail-on-removeListener-and-non-existent-event&highlight=mousewheel
			        }
			        ,dockedItems: [
			        {
			            xtype: 'toolbar',
			            items: [
				            {
				                text: 'Add Person'
				                ,iconCls: 'icon-add'
				                ,handler: factory.handleAddUser
				                
				            }
				            ,'-'
				            ,{
				                text: 'Delete Person',
				                iconCls: 'icon-delete',
				                handler: function(){
				                    var selection = this.up('gridpanel').getView().getSelectionModel().getSelection()[0];
				                    
				                    console.log(selection);
				                    
				                    if (selection) {
				                        this.up('gridpanel').remove(selection);
				                    }
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
            	} // grid panel end
				,{ // new panel to hold our form
					columnWidth: 0.4
					,xtype: 'panel'
					,collapsible: true
					,collapsed: true
					//,renderTo: 'savecancelrender'
		            ,region: 'east'
		            ,buttons: [{
				            text: 'Save'
				            //,renderTo: 'savecancelrender'
				            ,handler: function() {
				            	var form = this.up('form').getForm();
				            	
				            	if(form.isValid())
				            	{
				            		console.info('%o',form);
				            		
				            		form.submit({
					                    success: function(form, action) {
					                       Ext.Msg.alert('Success', 'Record Saved');
					                       this.up('form').getForm().reset();
					                    },
					                    failure: function(form, action) {
					                        Ext.Msg.alert('Failed', 'Record Not Saved');
					                    }
					                });
				            	}
				            
				            }
				        },{
				            text: 'Cancel'
				        }]
					,items: [{
			            margin: '0 0 0 10'
			            ,xtype: 'fieldset'
			            ,defaults: {
			                width: 240
			                ,labelWidth: 90
			            }
						,defaultType: 'textfield'
			            ,items: [
							{
				                fieldLabel: 'Email',
				                name: 'Email'
				            },{
				                fieldLabel: 'First Name',
				                name: 'FirstName'
				            },{
				                fieldLabel: 'Last Name',
				                name: 'LastName'
				            },{
				      			xtype: 'combobox'
				                ,fieldLabel: 'Account Level'
				                ,name: 'AccountLevel'
				                ,store: this.accountLevelDropdown
							    ,displayField: 'name'
							    ,valueField: 'value'
				            },{
				            	name: 'SaveCancelRender'
				            	,id: 'savecancelrender'
				            	,xtype: 'hidden'
				            }
			            ]
					}]
				}
			]
	   	});
	   	
		//return this.grid;
		return this.gridForm;
	}
	

});

