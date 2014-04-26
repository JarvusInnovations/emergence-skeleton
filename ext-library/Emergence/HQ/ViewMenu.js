Ext.define('Emergence.HQ.ViewMenu', {

	extend: 'Ext.Component'
	,requires: []

	,id: 'hq-view-menu'
	,width: 175
	,autoEl: {
		tag: 'ul'
	}

    ,initComponent: function(){
    
    	this.addEvents('menuClick');

        this.callParent(arguments);
    }

    ,initEvents: function() {

    }

	,loadMenuSpec: function(mSpecs) {
	
		this.update('');
		
		if(mSpecs.length)
		{
			var specs = [];
			Ext.each(mSpecs, function(menu) {
				specs.push({
					tag: 'li'
					,cn: {
						tag: 'a'
						,href: '#'+menu.id
						,cn: menu.title
					}
				});
			});
	
			this.getEl().setOpacity(0).createChild(specs);
			this.getEl().setOpacity(1, true);
		}
		
		
    	this.getEl().on('click', function(ev, t) {
    		ev.stopEvent();
    		this.fireEvent('menuClick', ev, t);
    	}, this, {delegate: 'a'});
	}
	
	,setCurrentTab: function(tabId) {
		var li = this.getEl().select('a[href=#'+tabId+']').first().up('li');
		
		li.radioCls('current');
		
		this.fireEvent('tabChange', tabId, li);
	}

});