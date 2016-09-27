/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.proxy.TreeRecords', {
    extend: 'Emergence.proxy.Records',
    alias: 'proxy.treerecords',

    connection: 'EmergenceConsole.proxy.API',

    //sortParam : false,

    buildRequest: function() {
        var me = this,
            request = me.callParent(arguments),
            params = request.getParams();

        if (request.getOperation().getId()=='root') {
            request.getOperation().setId('');
        }

        if (params) {
            delete params[me.getIdParam()];
        }

        return request;
    },

    buildUrl: function(request) {
        var me = this,
            readId = request.getOperation().getId(),
            baseUrl = me.getUrl(request),
            action = request.getAction();

        if (action=='read') {
            if (readId=='root') {
                return baseUrl;
            } else {
                return baseUrl += '/' + readId;
            }
        }

        return me.callParent(arguments);
    }

});
