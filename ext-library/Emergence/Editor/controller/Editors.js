Ext.define('Emergence.Editor.controller.Editors', {
	extend: 'Ext.app.Controller'
	,refs: [{
		ref: 'tabPanel'
		,selector: 'tabpanel'
	}]
	,aceModules: [
		'/jslib/ace/mode-javascript.js'
		,'/jslib/ace/mode-html.js'
		,'/jslib/ace/mode-php.js'
		,'/jslib/ace/mode-css.js'
    	,'/jslib/ace/themes/coda-emergence.js'
    	//,'/jslib/ace/themes/tomorrow-night.js'
	]
	,aceTheme: "ace/theme/coda-emergence"
	,views: ['editor.TabPanel','editor.ACE']
	,init: function()
    {
		//console.info('Emergence.Editor.controller.Editors.init()');

		// Start listening for events on views
		this.control({
			'emergence-editortabpanel': {
				tabchange: this.onTabChange
                ,staterestore: this.onTabsStateRestore
			}
		});
		
		this.application.on('fileopen', this.onFileOpen, this);
		this.application.on('filesave', this.onFileSave, this);
		this.application.on('fileclose', this.onFileClose, this);
        this.application.on('diffopen', this.onDiffOpen, this); 
		
		// load ACE javascripts
		this.application.aceReady = false;
		this.application.aceModulesLoaded = [];
		
		Ext.Loader.loadScriptFile('/jslib/ace/ace.js', function() {
			Ext.each(this.aceModules, function(moduleUrl) {
				Ext.Loader.loadScriptFile(moduleUrl, function() {
					this.application.aceModulesLoaded.push(moduleUrl);
					
					if(this.application.aceModulesLoaded.length == this.aceModules.length)
					{
						this.application.aceReady = true;
						this.application.fireEvent('aceReady')
					}
				}, Ext.emptyFn, this);
			}, this);
		}, Ext.emptyFn, this);
	}
	,onLaunch: function()
    {
		//console.info('Emergence.Editor.controller.Editors.onLaunch()');
	}
	,onTabChange: function(tabPanel, newCard, oldCanel)
    {
        var token = newCard.itemId;
           
        if(token)
            this.application.setActiveView(token, newCard.title);
            
		var activeCard = this.getTabPanel().getActiveTab();
		
		if(activeCard.xtype == 'aceeditor' && typeof activeCard.aceEditor != 'undefined')
		{
			activeCard.onResize();
		}  
	}
    ,onTabsStateRestore: function(tabPanel, state) {
        
        Ext.each(state.openFiles, function(path) {
            this.onFileOpen(path, false);      
        }, this);
        
    }
    ,onDiffOpen: function(path, autoActivate, sideA, sideB)
    {
        autoActivate = autoActivate !== false; // default to true 
        
        var itemId, title;
        
        title = path.substr(path.lastIndexOf('/')+1) + ' (' + sideA + '&mdash;' + sideB + ')';
        itemId = 'diff:[' + sideA + ',' + sideB + ']/'+path;
        
        var tab = this.getTabPanel().getComponent(itemId); 
        
        if(!tab)
        {
            tab = this.getTabPanel().add({
                xtype: 'emergence-diff-viewer'
                ,path: path
                ,sideAid: sideA
                ,sideBid: sideB
                ,title: title
                ,closable: true
                ,html: '<div></div>'
            });
        }
        
        if(autoActivate)
            this.getTabPanel().setActiveTab(tab);        
    }
	,onFileOpen: function(path, autoActivate, id, line) {
    
        autoActivate = autoActivate !== false; // default to true
	    
        var itemId, title;
        
        if(id)
        {
            itemId = 'revision:[' + id + ']/'+path;
            title = path.substr(path.lastIndexOf('/')+1) + '(' + id + ')';
        }
        else
        {
            itemId = '/' + path;   
            title = path.substr(path.lastIndexOf('/')+1);
        }
        
		var tab = this.getTabPanel().getComponent(itemId);
                   
        if(!tab)
		{
        	tab = this.getTabPanel().add({
	        	xtype: 'aceeditor'
	        	,path: path
	        	,aceTheme: this.aceTheme
	        	,title: title
	        	,closable: true
	        	,initialLine: line
                ,html: '<div></div>'
                ,revisionID: id
                ,persistent: !id
	        });
		}
        
        if(autoActivate) {
	    	this.getTabPanel().setActiveTab(tab);
        }
	}
	,onFileSave: function() {
	
		var activeCard = this.getTabPanel().getActiveTab();
		
		if(activeCard.xtype == 'aceeditor')
		{
			activeCard.saveFile();
		}
	}
	,onFileClose: function() {
	
		var activeCard = this.getTabPanel().getActiveTab();
		
		if(activeCard.closable)
		{
			activeCard.close();
		}
	}
});