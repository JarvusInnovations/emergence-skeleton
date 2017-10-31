/* jslint browser: true, undef: true, white: false, laxbreak: true *//* global Ext, EmergenceEditor*/
Ext.define('EmergenceEditor.store.ActivityStream', {
    extend: 'Ext.data.Store',
    alias: 'store.activitystream',
    requires: [
        'Jarvus.proxy.API'
    ],


    // ,autoLoad: true
    model: 'EmergenceEditor.model.ActivityEvent',
    proxy: {
        type: 'api',
        connection: 'EmergenceEditor.API',
        url: '/editor/activity',
        reader: {
            type: 'json',
            rootProperty: 'data'
        }
    }
});