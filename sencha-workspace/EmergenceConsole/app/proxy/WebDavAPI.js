/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.proxy.WebDavAPI', {
    extend: 'Ext.data.Connection',
    singleton: true,

    qualifiedUrlRe: /^(https?:)?\/\//,

    config: {
        /**
         * @cfg {String/null}
         * A host to prefix URLs with, or null to leave paths domain-relative
         */
        host: null,

        /**
         * @cfg {Boolean}
         * True to use HTTPS when prefixing host. Only used if {@link #cfg-host} is set
         */
        useSSL: false,

        /**
         * @cfg {String/null}
         * A path to prefix URLs with
         */
        pathPrefix: null,

        // @inheritdoc
        withCredentials: true,

        // @inheritdoc
        //useDefaultXhrHeader: false,

        // @inheritdoc
        disableCaching: false
    },

    //@private
    buildUrl: function(path) {
        var me = this,
            host = me.getHost(),
            pathPrefix = me.getPathPrefix();

        if (me.qualifiedUrlRe.test(path)) {
            return path;
        }

        if (pathPrefix) {
            path = pathPrefix + path;
        }

        if (host) {
            path = (me.getUseSSL() ? 'https://' : 'http://') + host + '/develop/' +path;
        }

        return path;
    },


    getFile: function(path, cb) {
        var me = this;

        me.request({
            method: 'get',
            url: me.buildUrl(path),
            headers: {
                'Accept': '*/*'
            },
            success: function(response) {
                var text = response.responseText,
                    contentType = response.getResponseHeader('content-type');

                if (cb && Ext.isFunction(cb)) {
                    cb.call(this, path, text, contentType);
                }
            }
        });
    },

    saveFile: function(path, text, cb) {
        var me = this;

        me.request({
            method: 'put',
            url: me.buildUrl(path),
            headers: {
                'Accept': '*/*'
            },
            rawData: text,
            callback: function(options, success, response) {
                if (cb && Ext.isFunction(cb)) {
                    cb.call(me, options, success, response);
                }
            }
        });
    },

    deleteNode: function(path, cb) {
        var me = this;

        me.request({
            method: 'delete',
            url: me.buildUrl(path),
            headers: {
                'Accept': '*/*'
            },
            callback: function(options, success, response) {
                if (cb && Ext.isFunction(cb)) {
                    cb.call(me, options, success, response);
                }
            }
        });
    },

    createNode: function(path, cb) {
        var me = this;

        me.request({
            method: 'put',
            url: me.buildUrl(path),
            headers: {
                'Accept': '*/*'
            },
            callback: function(options, success, response) {
                if (cb && Ext.isFunction(cb)) {
                    cb.call(me, path, options, success, response);
                }
            }
        });
    },

    createCollection: function(path, cb) {
        var me = this;

        me.request({
            method: 'mkcol',
            url: me.buildUrl(path),
            headers: {
                'Accept': '*/*'
            },
            callback: function(options, success, response) {
                if (cb && Ext.isFunction(cb)) {
                    cb.call(me, path, options, success, response);
                }
            }
        });
    },

    renameNode: function(path, newpath, cb) {
        var me = this;

        me.request({
            method: 'move',
            url: me.buildUrl(path),
            headers: {
                'Accept': '*/*',
                'Destination': me.buildUrl(newpath)
            },
            callback: function(options, success, response) {
                if (cb && Ext.isFunction(cb)) {
                    cb.call(me, options, success, response);
                }
            }
        });
    }
});
