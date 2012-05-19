Ext.define('Emergence.Editor.view.file.Tree', {
    extend: 'Ext.tree.Panel'
    ,alias: 'widget.emergence-filetree'
	,stateId: 'editor-filetree'
    ,store: 'FileTree'
    ,title: 'Filesystem'
    ,useArrows: true
    ,rootVisible: false
    ,autoScroll: true
    ,scrollDelta: 10
    ,multiSelect: true
    ,viewConfig: {
    	loadMask: false
    	,plugins: {
    		ptype: 'treeviewdragdrop'
    		,pluginId : 'ddplugin'
    		,appendOnly: true
    		,dragText : '{0} selected item{1}'
    	}
    }
});