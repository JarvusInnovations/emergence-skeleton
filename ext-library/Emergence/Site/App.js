Ext.define('Emergence.Site.App', {
	extend: 'Emergence.HQ.AbstractApp'
	,requires: ['Emergence.HQ.ViewMenu', 'Emergence.Site.People']
	,appId: 'site'
	,config: {
		title: 'People'
	}
	,defaultPerspective: 'People'
	,initApp: function(){
		//console.log(this);
		//console.info('people: %s', this.getTitle());
	}
	,loadGrid: function(items) {
		
		if(!this.SitePeopleFactory) {
			this.SitePeopleFactory = Ext.create('Emergence.Site.People');
		}
	
		
		var grid = this.SitePeopleFactory.createGrid();
	
		this.loadContent(grid);
	}
	,getPerspectives: function() {
		return [{
			id: 'people'
			,title: 'People'
		}];
	}
	,loadPerspective: function(link) {

		this.callParent(arguments);

		this.loadGrid();
	}
/*
	,onMenuClick: function(ev, t) {
		var li = ev.getTarget('li', null, true);
		
		li.radioCls('current');
		
		// match content container bg to tab BG
		this.viewContainer.viewContent.getEl().setStyle('background-color', li.getStyle('background-color'));

		switch(t.hash)
		{
			case '#activity':
				return this.loadActivity();
			case '#tasks':
				return this.loadTasks();
			case '#tickets':
				return this.loadTickets();	
			case '#hours':
				return this.loadHours();
		}
	}


	,loadActivity: function() {

		this.loadContent({
			xtype: 'component'
			,padding: 12
			,html: 'hours panel goes here'
		});
		
	}

	,loadTasks: function() {

		this.loadContent(Ext.create('Jarvus.Projects.TasksView'));

	}

	,loadTickets: function() {

		this.loadContent(Ext.create('Jarvus.Projects.TicketsView'));

	}

	,loadHours: function() {

		this.loadContent({
			xtype: 'component'
			,padding: 12
			,html: 'hours panel goes here'
		});

	}
*/

});