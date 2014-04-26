declare('Browser::BrowserPanel', function(use) {
    use(['Ext::ux::IFrameComponent'] , function(use) {


		Browser.BrowserPanel = Ext.extend(Ext.ux.IFrameComponent, {
		
			title: 'Browse Site'
			,tabTip: 'Browse the website within the management console'
			,url: window.location.href.substr(0, window.location.href.indexOf('/', 8)) + '?_framed=1'
			
			,initComponent: function() {
						
				Browser.BrowserPanel.superclass.initComponent.apply(this, arguments);
			}
			
			
			,onRender: function() {
						
				Browser.BrowserPanel.superclass.onRender.apply(this, arguments);
				
/*
				// read: http://www.google.com/search?q=site%3Aextjs.com+grouptabpanel+add+tab
				this.ownerCt.add({
					title: 'reload'
					,tabTip: 'Roload the current page'
					,html: 'foo bar'
				})
*/
			
			}
			
			
		
		});
		
		// register xtype to allow for lazy initialization
		Ext.reg('BrowserPanel', Browser.BrowserPanel);
		

	});
});

