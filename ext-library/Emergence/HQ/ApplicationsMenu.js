Ext.define('Emergence.HQ.ApplicationsMenu', {

	extend: 'Ext.Component'
	,requires: []
	,id: 'hq-app-list'
	,autoEl: {
		tag: 'ul'
	}

	,listeners: {
		scope: this
		,afterrender: function(appsMenu, options) {
			appsMenu.loadInitialApp();
		}
	}

    ,initComponent: function(){
    
    	this.addEvents('appSelected');

        this.callParent(arguments);
    }
	,loadInitialApp: function() {
		//console.log(this);
		
		//console.log(this.registeredApps);
	}
    ,createAppTab: function(appCfg) {
    	return this.getEl().createChild({
    		tag: 'li'
    		,cls: 'loading'
    	});
    }
    ,registeredApps: Array()
    ,loadApp: function(app, tab){

		this.registeredApps.push(app);

    	app.appTab = tab || this.createAppTab(app.config);
    	app.appTab.removeCls('loading').addCls(app.getAppId());
   
       	app.appLink = app.appTab.createChild({
			tag: 'a'
			,href: '/manage/'+app.appId
			,cn: app.getTitle()
			,style: { opacity: 0 }
		}).animate({ opacity: 1 });
		
		var appLinkWidth = app.appLink.getWidth();		
		app.appTab.animate({ width: appLinkWidth + 'px' });

		app.appLink.on('click', function(ev, t) {
			ev.stopEvent();

			this.appSelectedPreCheck(app.title);

			this.fireEvent('appSelected', app);
		}, this);
		
    	return app.appLink;
    }
	,appSelectedPreCheck: function(current) {
		Ext.each(this.registeredApps,function(app) {
			if(app.title == current) {
				return;
			}
			
			if(app.isLoaded) {
				app.onAppUnload();
			}
		});
	}
});