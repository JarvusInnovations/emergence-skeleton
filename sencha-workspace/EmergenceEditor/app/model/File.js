/* jslint browser: true, undef: true, white: false, laxbreak: true *//* global Ext, EmergenceEditor*/
Ext.define('EmergenceEditor.model.File', {
    extend: 'Ext.data.Model',


    idProperty: 'FullPath',
    fields: [{
        name: 'ID',
        type: 'integer'
    }, {
        name: 'Handle'
    }, {
        name: 'Status'
    }, {
        name: 'Created',
        type: 'date',
        dateFormat: 'timestamp'
    }, {
        name: 'CreatorID',
        type: 'integer'
    }, {
        name: 'ParentID',
        type: 'integer'
    }, {
        name: 'PosLeft',
        type: 'integer'
    }, {
        name: 'PosRight',
        type: 'integer'
    }, {
        name: 'Class'
    }, {
        name: 'FullPath'
    }, {
        name: 'leaf',
        type: 'boolean',
        depends: ['Class'],
        convert: function(v, r) {
            return r.get('Class') == 'SiteFile';
        }
    }],

    /*
     *   Default implementation tries to run destroy through the store just cause I asked for a refresh
     *   This work around is as awesome as it is since it cuts down the call stack considerably.
    */
    destroy: function() {
        this.remove(true);
    }
});