Ext.Loader.setConfig({
	enabled: true
	,paths: {
		Ext: '/x/ext/src'
		,Emergence: '/x/Emergence'
        ,'Ext.ux': '/x/ux'
	}
	//,disableCaching: false
});

Ext.application({
	name: 'Emergence.Editor'
	,appFolder: '/x/Emergence/Editor'
	,requires: ['Ext.util.KeyMap', 'Ext.state.LocalStorageProvider', 'Emergence.Editor.store.DavClient','Emergence.Editor.view.FullscreenViewport']
	//,models: ['Store']
	,controllers: ['Viewport','Files','Transfers','Editors','Revisions','Activity']
	,launch: function() {
		Emergence.Editor.app = this;
		
		// Create viewport
        if(location.search.match(/\Wfullscreen\W/))
        {
    	    this.viewport = Ext.create('Emergence.Editor.view.FullscreenViewport');
        }
        else
        {
        	// initialize state manager
    		Ext.state.Manager.setProvider(Ext.create('Ext.state.LocalStorageProvider'));
            
    	    this.viewport = Ext.create('Emergence.Editor.view.Viewport');
        }
		
		// remove loading class
		Ext.getBody().removeCls('loading');
        
        // get ref to title tag
        this.titleDom = document.querySelector('title');
	}
    
    // todo: make this ask the tab for the title and moving this to ontabchange 
    ,setActiveView: function(token, title) {
        Ext.util.History.add(token, true);
        this.titleDom.innerHTML = title + " - " + location.hostname;
    }
	
});