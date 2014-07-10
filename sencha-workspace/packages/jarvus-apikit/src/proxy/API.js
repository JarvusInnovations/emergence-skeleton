/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Jarvus.ext.proxy.API', {
    extend: 'Ext.data.proxy.Ajax',
    alias: 'proxy.api',
    requires: [
        'Jarvus.util.API'
    ],

    config: {
        /**
         * @cfg The API wrapper singleton that will process requests
         * @required
         */
        apiWrapper: 'Jarvus.util.API'
    },

    sendRequest: function(request) {
        var me = this,
            apiWrapper = me.apiWrapper;

        // ensure apiWrapper is required and converted to instance before request is built
        if (typeof apiWrapper == 'string') {
            Ext.syncRequire(apiWrapper);
            apiWrapper = me.apiWrapper = Ext.ClassManager.get(apiWrapper);
        }

        request.setRawRequest(apiWrapper.request(Ext.apply(request.getCurrentConfig(), {
            autoDecode: false,
            disableCaching: false
        })));

        me.lastRequest = request;

        return request;
    },

    getAPIWrapper: function() {
        return this.apiWrapper;
    }
});