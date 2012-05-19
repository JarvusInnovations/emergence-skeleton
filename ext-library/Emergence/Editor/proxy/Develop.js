Ext.define('Emergence.Editor.proxy.Develop', {
	extend: 'Ext.data.proxy.Ajax'
	,alias: 'proxy.develop'
	,requires: ['Ext.data.Request']
	
    ,url: '/develop/json/'
    ,noCache: false
    
    ,buildRequest: function(operation) {
    
    	var url = this.url;
    	
		if(typeof operation.node != "undefined" && operation.node.raw)
		{
			url = '/develop/json/' + operation.node.raw.FullPath;
		}
		else if(operation.records && operation.records[0].raw)
		{
			url = '/develop/json/' + operation.records[0].raw.FullPath;
		}
    	
		return Ext.create('Ext.data.Request', {
			action: operation.action//'Downloading file'//
			,records : operation.records
			,operation: operation
			,url: url
            ,task: 'directory-listing'
		});
	}



	,doRequest: function(operation, callback, scope) {
		var writer  = this.getWriter()
			,request = this.buildRequest(operation, callback, scope);
	
		Ext.apply(request, {
			headers       : this.headers,
			timeout       : this.timeout,
			scope         : this,
			callback      : this.createRequestCallback(request, operation, callback, scope),
			method        : this.getMethod(request),
			disableCaching: false // explicitly set it to false, ServerProxy handles caching
		});
	
		Emergence.Editor.store.DavClient.request(request);
	
		return request;
	}
});