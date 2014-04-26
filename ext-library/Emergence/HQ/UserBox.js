Ext.define('Emergence.HQ.UserBox', {

	extend: 'Ext.Component'
	
	,id: 'hq-user'
	
	,html: [{
		tag: 'a'
		,href: '/profile/view'
		,cls: 'user'
		,cn: [{
			tag: 'img'
			,src: '/thumbnail/'+(User.data.PrimaryPhotoID?User.data.PrimaryPhotoID:'person')+'/99x24'
		},{
			tag: 'span'
			,cls: 'user-name has-submenu'
			,html: User.data.FirstName+' '+User.data.LastName
		}]
	},{
		tag: 'a'
		,href: '/logout'
		,cls: 'logout'
		,id: 'logout-link'
		,html: 'Log Out'
	}]
	
	,initEvents: function() {
	
		var el = this.getEl()
			,userLink = el.child('.user');
			
		userLink.on('click', function(ev, t) {
			ev.stopEvent();
			console.info('click user');
		}, this);
		
		userLink.child('img').on('load', function(ev, t) {
			this.getEl().setStyle('width', 'auto');
			this.doComponentLayout();
		}, this);
	
/*
		this.getEl().on('mouseenter', function() {
			
			if(!this.userMenu)
				this.userMenu = this.buildUserMenu();
				
			this.userMenu.getEl().alignTo(this.getEl(), 'bl');
			this.userMenu.show();
			
		}, this);
*/
	
	}
	
	
	,buildUserMenu: function() {
		return Ext.create('Ext.Container', {
			floating: true
			,hidden: true
			,items: [{
				xtype: 'box'
				,html: 'foo'
			},{
				xtype: 'box'
				,html: 'bar'
			}]
		});
	}

});