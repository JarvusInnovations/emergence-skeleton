Ext.define('EmergenceEditor.view.TransfersGrid', {
    extend: 'Ext.grid.Panel',
    xtype: 'emergence-transfersgrid',


    title: 'Transfers',
    sortableColumns: false,
    enableColumnHide: false,
    enableColumnMove: false,
    columns: {
        defaults: {
            menuDisabled: true
        },
        items: [
            { header: 'Task',
                dataIndex: 'task',
                width: 150 },
            { header: 'Path',
                dataIndex: 'path',
                flex: 1 },
            { header: 'Info',
                dataIndex: 'info',
                flex: 1 },
            { header: 'Status',
                dataIndex: 'status',
                width: 150 }
        ]
    }
});