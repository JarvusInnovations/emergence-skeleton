/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Emergence.ext.proxy.Records', {
    extend: 'Jarvus.ext.proxy.API',
    alias: 'proxy.records',
    requires: [
        'Emergence.util.API'
    ],

    apiWrapper: 'Emergence.util.API',

    /**
     * @cfg The base URL for the managed collection (e.g. '/people')
     * @required
     */
    url: null,

    idParam: 'ID',
    pageParam: false,
    startParam: 'offset',
    limitParam: 'limit',
    sortParam: 'sort',
    simpleSortMode: true,
    groupParam: false,
    reader: {
        type: 'json',
        root: 'data',
        totalProperty: 'total'
    },
    writer:{
        type: 'json',
        root: 'data',
        writeAllFields: false,
        allowSingle: false
    },

    buildRequest: function(operation) {
        var me = this,
            params = operation.params = Ext.apply({}, operation.params, me.extraParams),
            request = new Ext.data.Request({
                action: operation.action,
                records: operation.records,
                operation: operation,
                params: Ext.applyIf(params, me.getParams(operation)),
                headers: me.headers
            });

        request.method = me.getMethod(request);
        request.url = me.buildUrl(request);
        
        // compatibility with Jarvus.ext.override.proxy.DirtyParams since we're entirely replacing the buildRequest method it overrides
        if (Ext.isFunction(me.clearParamsDirty)) {
            me.clearParamsDirty();
        }

        return request;
    },

    buildUrl: function(request) {
        var me = this,
            baseUrl = me.getUrl(request) ;

        switch(request.action) {
            case 'read':
                if (request.operation.id && (me.idParam == 'ID' || me.idParam == 'Handle')) {
                    baseUrl += '/' + encodeURIComponent(request.operation.id);
                }
                break;
            case 'create':
            case 'update':
                baseUrl += '/save';
                break;
            case 'destroy':
                baseUrl += '/destroy';
                break;
        }

        return baseUrl;
    },

    getParams: function(operation) {
        var me = this,
            include = me.include,
            relatedTable = me.relatedTable,
            idParam = me.idParam,
            params = me.callParent(arguments);

        if (operation.id && idParam != 'ID') {
            params[idParam] = operation.id;
        }
        
        if (include) {
            params.include = Ext.isArray(include) ? include.join(',') : include;
        }
        
        if (relatedTable) {
            params.relatedTable = Ext.isArray(relatedTable) ? relatedTable.join(',') : relatedTable;
        }
        
        if (me.summary) {
            params.summary = 'true';
        }
        
        return params;
    }
});