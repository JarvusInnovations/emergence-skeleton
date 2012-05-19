Ext.define('Emergence.Editor.view.contextmenu.MultiNodeMenu', {
    extend: 'Ext.menu.Menu'
    ,alias: 'widget.emergence-multinodemenu'
    ,stateful: false
    ,width: 130
	,items: [
		{
		    text: 'Open'
            ,action: 'open'
            ,icon: '/img/icons/fugue/blue-folder-horizontal-open.png'
	    },{
		    text: 'Delete'
            ,action: 'delete'
            ,icon: '/img/icons/fugue/cross.png'
	    }
    ]
});