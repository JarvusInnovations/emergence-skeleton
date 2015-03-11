/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Emergence.ext.proxy.Records', {
    extend: 'Jarvus.ext.proxy.API',
    alias: 'proxy.records',
    requires: [
        'Emergence.util.API'
    ],

    config: {
        connection: 'Emergence.util.API',
        include: null,
        relatedTable: null,
        summary: false
    },

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
    reader: {
        type: 'json',
        rootProperty: 'data',
        totalProperty: 'total',
        messageProperty: 'message'
    },
    writer:{
        type: 'json',
        rootProperty: 'data',
        writeAllFields: false,
        allowSingle: false
    },

    buildRequest: function(operation) {
        var me = this,
            initialParams = Ext.apply({}, operation.getParams()),
            // Clone params right now so that they can be mutated at any point further down the call stack
            params = Ext.applyIf(initialParams, me.getExtraParams() || {}),
            request = new Ext.data.Request({
                action: operation.getAction(),
                records: operation.getRecords(),
                operation: operation,
                params: Ext.applyIf(params, me.getParams(operation)),
                headers: me.headers
            });

        request.setMethod(me.getMethod(request));
        request.setUrl(operation.config.url || me.buildUrl(request));

        // compatibility with Jarvus.ext.override.proxy.DirtyParams since we're entirely replacing the buildRequest method it overrides
        if (Ext.isFunction(me.clearParamsDirty)) {
            me.clearParamsDirty();
        }

        operation.setRequest(request);

        return request;
    },

    buildUrl: function(request) {
        var me = this,
            readId = request.getOperation().getId(),
            idParam = me.getIdParam(),
            baseUrl = me.getUrl(request) ;

        switch(request.getAction()) {
            case 'read':
                if (readId && (idParam == 'ID' || idParam == 'Handle')) {
                    baseUrl += '/' + encodeURIComponent(readId);
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
            include = me.getInclude(),
            relatedTable = me.getRelatedTable(),
            summary = me.getSummary(),
            idParam = me.idParam,
            id = operation.getId(),
            params = me.callParent(arguments);

        if (id && idParam != 'ID') {
            params[idParam] = id;
        }

        if (include) {
            params.include = Ext.isArray(include) ? include.join(',') : include;
        }

        if (relatedTable) {
            params.relatedTable = Ext.isArray(relatedTable) ? relatedTable.join(',') : relatedTable;
        }

        if (summary) {
            params.summary = 'true';
        }

        return params;
    }
});