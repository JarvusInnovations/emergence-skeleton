Ext.define('EmergenceEditor.view.menu.File', {
    extend: 'Ext.menu.Menu',
    xtype: 'emergence-menu-file',
    requires: [
        'Ext.menu.Separator',

        /* global EmergenceEditor */
        'EmergenceEditor.API'
    ],


    config: {
        file: null,

        plain: true
    },

    items: [
        {
            text: 'Edit',
            action: 'edit',
            iconCls: 'x-fa fa-file-text-o',
            href: '#'
        },
        {
            text: 'Open File',
            action: 'open-file',
            iconCls: 'x-fa fa-file-o',
            hrefTarget: '_blank',
            href: '#'
        },
        {
            text: 'Open URL',
            action: 'open-url',
            iconCls: 'x-fa fa-external-link',
            hrefTarget: '_blank',
            href: '#'
        },
        {
            text: 'Rename',
            action: 'rename',
            iconCls: 'x-fa fa-pencil'
        },
        {
            text: 'Delete',
            action: 'delete',
            iconCls: 'x-fa fa-trash'
        },
        {
            xtype: 'menuseparator'
        },
        {
            itemId: 'detailsCmp',

            xtype: 'component',
            autoEl: 'table',
            tpl: [
                '<tr>',
                '    <th align="right">ID</th>',
                '    <td>{ID}</td>',
                '</tr>',
                '<tr>',
                '    <th align="right">Timestamp</th>',
                '    <td>{Timestamp:date("Y-m-d H:i:s")}</td>',
                '</tr>',
                '<tr>',
                '    <th align="right">Size</th>',
                '    <td>{Size:number("0,000")} bytes</td>',
                '</tr>',
                '<tr>',
                '    <th align="right">Hash</th>',
                '    <td>{SHA1:substr(0,8)}</td>',
                '</tr>',
                '<tr>',
                '    <th align="right">Type</th>',
                '    <td>{Type}</td>',
                '</tr>'
            ]
        }
    ],


    // config handlers
    updateFile: function(file) {
        var me = this,
            editItem = me.child('[action=edit]'),
            openFileItem = me.child('[action=open-file]'),
            openUrlItem = me.child('[action=open-url]'),
            filePath = file.get('FullPath'),
            editToken = '#/'+filePath,
            openUrl = EmergenceEditor.API.buildUrl('/develop/'+filePath),
            isInSiteRoot = filePath.indexOf('site-root/') === 0 || filePath.indexOf('_parent/site-root/') === 0,
            fileUrl;

        if (editItem.rendered) {
            editItem.itemEl.set({
                href: editToken
            });
        } else {
            editItem.href = editToken;
        }

        if (openFileItem.rendered) {
            openFileItem.itemEl.set({
                href: openUrl
            });
        } else {
            openFileItem.href = openUrl;
        }

        openUrlItem.setDisabled(!isInSiteRoot);

        if (isInSiteRoot) {
            fileUrl = EmergenceEditor.API.buildUrl('/'+filePath.replace(/^(_parent\/)?site-root\/(.*?)(\.php)?$/, '$2'));

            if (openUrlItem.rendered) {
                openUrlItem.itemEl.set({
                    href: fileUrl
                });
            } else {
                openUrlItem.href = fileUrl;
            }
        }

        me.getComponent('detailsCmp').setData(file.getData());
    }
});