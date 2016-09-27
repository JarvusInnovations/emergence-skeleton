/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.model.file.File', {
    //extend: 'Ext.data.TreeModel',
    extend: 'Ext.data.Model',
    requires: [
        'EmergenceConsole.proxy.TreeRecords'
    ],

    proxy: {
        type: 'treerecords',
        idParam: null,
        url: '/develop/json',
        reader: {
            type: 'json',
            rootProperty: 'children'
        }
    },

    idProperty: 'FullPath',

    fields: [{
        name: 'ID',
        type: 'integer'
    },{
        name: "Class",
        type: "string"
    },{
        name: 'Handle'
    },{
        name: 'Status'
    },{
        name:  'Created',
        type: 'date',
        dateFormat: 'timestamp'
    },{
        name: 'CreatorID',
        type: 'integer'
    },{
        name: 'ParentID',
        type: 'integer'
    },{
        name: 'PosLeft',
        type: 'integer'
    },{
        name: 'PosRight',
        type: 'integer'
    },{
        name: 'Class'
    },{
        name: 'FullPath'
    },{
        name: 'text',
        type: 'string',
        convert: function(v, r) {
            var handle = r.get('Handle');

            if (handle) {
                return handle;
            } else {
                return '[[Unknown Node]]';
            }
        }
    },{
        name: 'leaf',
        type: 'boolean',
        convert: function(v, r) {
            return (r.get('Class')=='SiteFile'?true:false);
        }
    }]
    // TODO: still necessary??
    /*
     *   Default implementation tries to run destroy through the store just cause I asked for a refresh
     *   This work around is as awesome as it is since it cuts down the call stack considerably.
    */
    //destroy: function() {
    //    this.remove(true);
    //}
});
