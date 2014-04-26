/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Jarvus.ext.proxy.API', {
    extend: 'Ext.data.proxy.Ajax',
    alias: 'proxy.api',
    
    /**
     * @cfg The API wrapper singleton that will process requests
     * @required
     */
    apiWrapper: 'Jarvus.util.API',

    doRequest: function() {
        var me = this,
            apiWrapper = me.apiWrapper,
            doApiRequest = me.doApiRequest,
            requestArguments = arguments;

        // ensure apiWrapper is required and converted to instance before request is built
        if (typeof apiWrapper == 'string') {
            Ext.require(apiWrapper, function() {
                me.apiWrapper = Ext.ClassManager.get(apiWrapper);
                doApiRequest.apply(me, requestArguments);
            });
        } else {
            doApiRequest.apply(me, requestArguments);
        }
    },
    
    doApiRequest: function(operation, callback, scope) {
        var me = this,
            writer = me.getWriter(),
            request = me.buildRequest(operation);
            
        if (operation.allowWrite()) {
            request = writer.write(request);
        }
        
        me.getAPIWrapper().request(Ext.apply(request, {
            autoDecode: false,
            disableCaching: false,
            success: function(response) {
                me.processResponse(true, operation, request, response, callback, scope);
            }
        }));
    },

    getAPIWrapper: function() {
        return this.apiWrapper;
    }
});