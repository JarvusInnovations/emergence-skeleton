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

/*
Class
:
"SiteCollection"
Created
:
"2017-09-05 09:40:18"
CreatorID
:
"1"
FullPath
:
"site-root/css"
Handle
:
"css"
ID
:
"239"
ParentID
:
"25"
PosLeft
:
"316"
PosRight
:
"327"
Site
:
"Local"
Status
:
"Normal"
6
:
{ID: "2692", CollectionID: "25", Handle: "LEGACY.md", Status: "Normal",…}
AncestorID
:
"2666"
AuthorID
:
"1"
Class
:
"SiteFile"
CollectionID
:
"25"
FullPath
:
"site-root/LEGACY.md"
Handle
:
"LEGACY.md"
ID
:
"2692"
SHA1
:
"2888f7b6cb235c08b90cfaee872825de851e8ee6"
Size
:
"351"
Status
:
"Normal"
Timestamp
:
"2017-11-24 15:36:39"
Type
:
"text/plain"
*/