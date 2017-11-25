Ext.define('EmergenceEditor.model.FilesystemNode', {
    extend: 'Ext.data.Model',


    idProperty: 'FullPath',

    fields: [
        // common propeerties
        {
            name: 'ID',
            type: 'integer'
        },
        {
            name: 'Class',
            type: 'string'
        },
        {
            name: 'Handle',
            type: 'string'
        },
        {
            name: 'FullPath',
            type: 'string'
        },
        {
            name: 'Status',
            type: 'string'
        },
        {
            name: 'leaf',
            type: 'boolean',
            depends: ['Class'],
            convert: function(v, r) {
                return r.get('Class') == 'SiteFile';
            }
        },
        {
            name: 'renaming',
            defaultValue: false,
            persist: false
        },

        // collection properties
        {
            name: 'Created',
            type: 'date',
            dateFormat: 'Y-m-d H:i:s',
            allowNull: true
        },
        {
            name: 'CreatorID',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'ParentID',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'PosLeft',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'PosRight',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'Site',
            type: 'string',
            allowNull: true
        },

        // file properties
        {
            name: 'Timestamp',
            type: 'date',
            dateFormat: 'Y-m-d H:i:s',
            allowNull: true
        },
        {
            name: 'AuthorID',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'CollectionID',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'SHA1',
            type: 'string',
            allowNull: true
        },
        {
            name: 'Size',
            type: 'integer',
            allowNull: true
        },
        {
            name: 'Type',
            type: 'string',
            allowNull: true
        }
    ],

    toUrl: function() {
        return '/' + this.get('FullPath');
    }
});