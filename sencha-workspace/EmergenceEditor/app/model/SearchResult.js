Ext.define('EmergenceEditor.model.SearchResult', {
    extend: 'Ext.data.Model',


    fields: [
        {
            name: 'File'
        },
        {
            name: 'line',
            type: 'integer'
        },
        {
            name: 'result'
        }
    ]
});