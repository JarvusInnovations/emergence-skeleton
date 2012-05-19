Ext.define('Emergence.Editor.view.contextmenu.TabMenu', {
    extend: 'Ext.menu.Menu'
    ,alias: 'widget.emergence-tabmenu'
	,items: [
        {
		    text: 'Close Tab'
            ,action: 'close-tab'
	    },{
		    text: 'Close Other Tabs'
            ,action: 'close-other-tabs'
	    },{
            xtype: 'menuseparator'
        },{
		    text: 'Open in New Window'
            ,action: 'open-in-new-window' 
	    },{
		    xtype: 'menuseparator'
	    },{
		    text: 'Show in File Browser'
            ,action: 'show-in-file-browser'
	    }
    ]
});