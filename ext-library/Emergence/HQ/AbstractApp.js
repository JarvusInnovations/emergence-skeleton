Ext.define('Emergence.HQ.AbstractApp', {

	requires: []
	,mixins: {
		observable: 'Ext.util.Observable'
	}
    
    ,isApp: true
    ,initApp: Ext.emptyFn
    ,isLoaded: false
    ,constructor: function(config, availableCallback) {
    	this.initConfig(config);
    	
    	if(!this.appId)
    		throw 'Emergence.HQ.AbstractApp subclass must implement appId';
 
    	
    	this.initApp();

    	this.callParent(arguments);
    	
    	if(availableCallback)
    		availableCallback(this);

    }
    
    ,getAppId: function() {
    	return this.appId;    
    }
    
    ,getPerspectives: function() {    
    	return false;
    }
    
    ,loadPerspective: function(link, viewContainer) {
        this.isLoaded = true;

		this.viewContainer = viewContainer;
		
		this.viewContainer.viewMenu.on('menuClick', this.onMenuClick, this);
    }
    
	,onMenuClick: function(ev, t) {
	
		var tabId = t.hash.substr(1);
		this.loadTab(tabId);
	}
	
	,onAppUnload: function() {
    	if(this.viewContainer)
    	{
    		this.viewContainer.viewMenu.un('menuClick', this.onMenuClick, this);
     	}
     	this.isLoaded = false;
	}
	
    ,loadContent: function(items) {
    	this.viewContainer.viewContent.removeAll();
    	this.viewContainer.viewContent.add(items);
    }
    
	,loadTab: function(tabId) {
		this.viewContainer.viewMenu.setCurrentTab(tabId);
	}
    
});