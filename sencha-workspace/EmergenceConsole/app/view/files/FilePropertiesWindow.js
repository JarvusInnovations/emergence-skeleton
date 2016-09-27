/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('EmergenceConsole.view.files.FilePropertiesWindow', {
    extend: 'Ext.window.Window',
    xtype: 'files-propertieswindow',

    layout: 'fit',
    border: 0,
    closeAction: 'hide',
    shadow: false,

    items: [{
        xtype: 'panel',
        itemId: 'content',
        margin: 10,
        tpl : [
            '<table>',
            '<tr><td>ID</td><td>{ID}</td></tr>',
            '<tr><td>Handle</td><td>{Handle}</td></tr>',
            '<tr><td>SHA1</td><td>{SHA1}</td></tr>',
            '<tr><td>Status</td><td>{Status}</td></tr>',
            '<tr><td>Size</td><td>{Size}</td></tr>',
            '<tr><td>Type</td><td>{Type}</td></tr>',
            '<tr><td>Timestamp</td><td>{Timestamp}</td></tr>',
            '<tr><td>CollectionID</td><td>{CollectionID}</td></tr>',
            '<tr><td>AncestorID</td><td>{AncestorID}</td></tr>',
            '<tr><td>AuthorID</td><td>{AuthorID}</td></tr>',
            '<tr><td>Class</td><td>{Class}</td></tr>',
            '<tr><td>Fullpath</td><td>{FullPath}</td></tr>',
            '</table>'
        ],
        data: []
    }]

});
