Ext.define('Emergence.HQ.ViewContainer', {

	extend: 'Ext.Container'
	,requires: ['Emergence.HQ.ViewMenu']
	,hidden: true
	
    ,initComponent: function(){
    
    	this.viewMenu = Ext.create('Emergence.HQ.ViewMenu', {
    		listeners: {
    			scope: this
    			,tabChange: this.onTabChange
    		}
    	});
    
    	this.viewContent = Ext.create('Ext.Container', {
    		id: 'hq-view-content'
    		,flex: 1
    		,layout: 'fit'
    	});
    
    	Ext.apply(this, {
			id: 'hq-view-container'
			,layout: {
				type: 'hbox'
				,align: 'stretch'
			}
			,items: [this.viewMenu, this.viewContent]
    	});
        
                        
        this.callParent(arguments);
    }
    
    
    ,loadAppPerspective: function(app, link) {
    	
    	if(!this.isVisible())
    	{
	    	this.show();
	    	this.getEl().setOpacity(0).setOpacity(1, true);
	    }
	    
    	app.loadPerspective(link, this);
    
    }
    
    ,onTabChange: function(tabId, li) {
		// match content container bg to tab BG
		this.viewContent.getEl().setStyle('background-color', li.getStyle('background-color'));
    }
});