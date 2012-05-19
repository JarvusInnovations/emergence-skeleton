Ext.define('Emergence.Editor.store.FileTree', {
    extend: 'Ext.data.TreeStore'
	,alias: 'store.filetree'
	,requires: ['Emergence.Editor.proxy.Develop']
	
	
    ,model: 'Emergence.Editor.model.File'
	
	,folderSort: true
	,sortOnLoad: true
	,sorters: [{
		property: 'Handle'
		,direction: 'ASC'
	}]
	
    ,root: {
        text: 'children'
        ,id: 'children'
        ,expanded: true
    }
    ,proxy: {
        type: 'develop'
    }
    ,refreshNodeByRecord: function(record)
    {
        this.load({
            node: record
        });     
    }
    //,clearOnLoad: false
    /*
    // proxy INSTANCE was required when trying to parse an XML response who's root wasn't "children"
    ,constructor: function() {
    	this.proxy = Ext.create('Emergence.Editor.proxy.Develop');
    	
    	return this.callParent(arguments);
    }
    */
});