declare('MICS', function() {
	
	// enable quick tips
	Ext.QuickTips.init();
	
	// setup state manager
	this.cookieProvider = new Ext.state.CookieProvider({
		path: '/'
	});
	
	Ext.state.Manager.setProvider(this.cookieProvider);
	
	// listen for forbidden errors
	Ext.Ajax.on('requestexception', function(conn, response, options) {
		
		if(response.getResponseHeader('X-Response-ID') == 'login')
		{
			Ext.Msg.alert('Session Expired', 'Your session has expired. You must reload this page and login back in to save changes.');
		}
		
		if(response.status == 403)
		{
			Ext.Msg.alert('Session Expired', 'Access forbidden. You must reload this page and login back in to save changes.');
		}
				

	});
	
	
	MICS = {
	
		openRecord: function(record, options) {
		
			options = Ext.apply({
				newWindow: false
				,pathAppend: false
				,hash: false
			}, options);
			
			var url = MICS.getRecordURL(record);
			
			if(options.pathAppend)
				url += options.pathAppend;
		
			if(options.hash)
				url += '#'+options.hash;
		
			if(options.newWindow)
				window.open(url);
			else
				window.location = url;		
		}
		
		,getRecordURL: function(record) {
		
			return this.getClassHandler(record.get('Class')) +'/'+record.get('Handle');

		}
		
		,getClassHandler: function(className) {
			switch(className)
			{
				case 'CMS_BlogPost':
					return '/blog';
					
				case 'CMS_Page':
					return '/pages';
					
				case 'CMS_Feature':
					return '/features';
			}
		}
		
		,errorResponseToText: function(obj) {
			var msg = '<strong>' + ( obj.message ? obj.message : 'There was a problem saving your changes' ) +'</strong>';
			
			var errRecords;
			if(obj.failed)
			{
				errRecords = obj.failed;
			}
			else if(obj.data && obj.data.validationErrors)
			{
				errRecords = [obj.data];
			}
			
			if(errRecords && errRecords.length)
			{
				msg += ':<ul>'
				for(var i = 0; i < errRecords.length; i++)
				{
					for (field in errRecords[i].validationErrors)
					{
						msg += '<li>'+errRecords[i].validationErrors[field]+'</li>';
					}
				}
				msg += '</ul>';
			}
			
			return msg;
		}
	};
	

});