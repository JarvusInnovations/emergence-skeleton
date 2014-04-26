Ext.define('Emergence.HQ.PerspectivesMenu', {

	extend: 'Ext.Component'
	
	,id: 'hq-perspectives'
	,height: 30
	,hidden: true
	
	,autoEl: {
		tag: 'ul'
	}
	
	,initEvents: function() {
	
		this.getEl().on('click', function(ev, t) {
			ev.stopEvent();
			console.log(t);
			this.fireEvent('perspectiveSelected', t);
		}, this, {delegate: 'a'})
	
	}
	
	,loadPerspectives: function(perspectives, app) {
	
		var spec = [];
		Ext.each(perspectives, function(p) {
		
			// build perspective link spec
			var pSpec = {
				tag: 'li'
				,cls: 'perspective'
				,cn: [{
					tag: 'a'
					,href: '/manage/'+app.getAppId()+'/'+p.id
					,cn: [p.title]
				}]
			};
			
			// build submenu
			if(p.menu)
			{
				pSpec.cn[0].cls = 'has-submenu';
				var menuSpec = {
					tag: 'ul'
					,cls: 'submenu'
					,cn: []
				};
				
				Ext.each(p.menu, function(m) {
					menuSpec.cn.push({
						tag: 'li'
						,cn: {
							tag: 'a'
							,cls: 'subperspective'
							,href: '/manage/'+app.getAppId()+'/'+m.id
							,cn: m.title
						}
					});
				});
				
				pSpec.cn.unshift(menuSpec);
			}
			
			// push to list
			spec.push(pSpec);
			
		}, this);
		
		this.getEl().update('').createChild(spec);
		
		this.show();
		
		this.fireEvent('perspectiveLoaded', app);
	}
	
	,show: function() {
		if(!this.isVisible())
		{
			this.getEl().setStyle({
				marginTop: '-'+this.self.prototype.height+'px'
				,opacity: 0
				,zIndex: 98
			});
			this.callParent().getEl().animate({
				marginTop: 0
				,opacity: 1
				,zIndex: 100
			});
		}
	}
	
	,hide: function() {
		if(this.isVisible())
		{
			this.getEl()
				.setStyle('z-index', 98)
				.animate({
					marginTop: -1*this.self.prototype.height
					,opacity: 0
					,callback: function() {
						Ext.Component.prototype.hide.apply(this);
					}
					,scope: this
				});
		}	
	}
});