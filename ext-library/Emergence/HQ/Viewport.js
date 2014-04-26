Ext.define('Emergence.HQ.Viewport', {

	extend: 'Ext.container.Viewport'
	,requires: [
		,'Emergence.HQ.SearchForm'
		,'Emergence.HQ.ApplicationsMenu'
		,'Emergence.HQ.UserBox'
		,'Emergence.HQ.PerspectivesMenu'
		,'Emergence.HQ.ViewContainer'
		,'Emergence.HQ.AbstractApp'
	]
	
	,listeners: {
		scope: this
		,appsloaded: function(viewport) {
/*
			Ext.each(viewport.apps.items, function(item) {
				if(item.title == 'Overview')
				{
					viewport.appsMenu.appSelectedPreCheck(item.title);

					viewport.appsMenu.fireEvent('appSelected', item);
				}
			});
*/
		}
	}
	,config: {
		siteSearch: true
	}
	,constructor: function(config) {
	
		// apply configuration
		this.initConfig(config);
		
		return this.callParent(arguments);
	}
	
	,initComponent: function(){
	
		// hack to fix broken feature detection, on the assumption that we are all using browsers supporting border-radius and linear gradients
		// but we should figure out why it's not detecting these capabilities correctly
		Ext.getBody().removeCls('x-nlg');
		Ext.getBody().removeCls('x-nbr');

		// create top-level interface elements
		this.appsMenu = Ext.create('Emergence.HQ.ApplicationsMenu', {flex: 1});
		this.userBox = Ext.create('Emergence.HQ.UserBox');
		this.perspectivesMenu = Ext.create('Emergence.HQ.PerspectivesMenu');
		this.viewContainer = Ext.create('Emergence.HQ.ViewContainer', {flex: 1});

		this.headerItems = [
			this.appsMenu
			,this.userBox
		];
		
		if(this.getSiteSearch())
		{
			this.searchForm = Ext.create('Emergence.HQ.SearchForm');
			this.headerItems.unshift(this.searchForm);
		}


		// configure viewport
		Ext.apply(this, {
			id: 'hq-viewport'
			,listeners: {
				scope: this
				,afterrender: this.initApps
			}
			,layout: {
				type: 'vbox'
				,align: 'stretch'
			}
			,items: [
				{
					id: 'hq-header'
					,autoEl: {tag: 'header'}
					,xtype: 'container'
					,height: 36
					,layout: {
						type: 'hbox'
						,align: 'stretch'
					}
					,items: this.headerItems
					,style: { opacity: 0 }
				}
				,this.perspectivesMenu
				,this.viewContainer
			]
		});

		this.callParent(arguments);
	}
	
	,initEvents: function() {
	
		this.relayEvents(this.appsMenu, ['appSelected']);
		this.relayEvents(this.perspectivesMenu, ['perspectiveSelected','perspectiveLoaded']);
		
		this.appsMenu.on('appSelected', this.onAppSelected, this)
		this.perspectivesMenu.on('perspectiveSelected', this.onPerspectiveSelected, this)
		this.perspectivesMenu.on('perspectiveLoaded', this.onPerspectiveLoaded, this)
	
		this.callParent(arguments);
	}

	// runs once for all apps
	,initApps: function() {
	
		// remove loading class from body
		Ext.getBody().removeCls('loading');
		Ext.get('hq-header').animate({ duration: 1000, opacity: 1 });
	
		var appCfgs = this.apps;
		
		this.appsCount = appCfgs.length;
		
    	this.apps = Ext.create('Ext.util.MixedCollection');
    	
    	Ext.each(appCfgs, this.loadApp, this);
    	
	}


	,loadApp: function(appCfg) {
		var viewport = this;
		
		if(typeof appCfg == 'string')
			appCfg = {appClass: appCfg};

		var tab = viewport.appsMenu.createAppTab(appCfg)
			,filePath = Ext.Loader.getPath(appCfg.appClass);


		Ext.Loader.loadScriptFile(filePath, function() {
			Ext.create(appCfg.appClass, appCfg, function(app) {
				
				viewport.apps.add(app);
				viewport.appsMenu.loadApp(app, tab);
				
				if(viewport.appsCount == viewport.apps.length) {
					viewport.fireEvent('appsloaded', viewport);	
				}				
			});
		}, false, false);
	}

	// runs once per app click on
	,onAppSelected: function(app) {
	
	
		this.currentApp = app;
		
		app.appTab.radioCls('current');
		
		//this.appContainer.getEl().mask('Loading application: '+app.getTitle(), 'x-mask-loading');
		
		var p = app.getPerspectives();
		
		if(p)
		{
			this.perspectivesMenu.loadPerspectives(p, app);
		}
		else
		{
			this.perspectivesMenu.hide(true);
			
			// load default perspective immediately
			this.currentPerspective = this.viewContainer.loadAppPerspective(this.currentApp);
		}
	}
	
	
	,onPerspectiveSelected: function(link) {
		this.currentPerspective = this.viewContainer.loadAppPerspective(this.currentApp, link);
		
		Ext.fly(link).parent('li').radioCls('current');
	
	}
	
	,onPerspectiveLoaded: function(app) {
		
		var menuItems = this.perspectivesMenu.getEl().select('.perspective');
		
		Ext.each(menuItems.elements, function(el) {
			var link = Ext.get(el).down('a');
			
			if(link.dom.innerHTML == app.defaultPerspective) {
				this.onPerspectiveSelected(link.dom);
			}
		}, this);
	}

});