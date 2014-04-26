Ext.define('Emergence.HQ.SearchForm', {

	extend: 'Ext.Container'
	
	,id: 'hq-search'
	,width: 175

	,autoEl: {tag: 'form'}	
	
	,initComponent: function(){
	
		/*
Ext.apply(this, {
			items: {
				xtype: 'textfield'
				,hideLabel: true
				,cls: 'search'
				,emptyText: 'Find stuff'
			}
		});
*/
		
		this.callParent(arguments);
	}
});