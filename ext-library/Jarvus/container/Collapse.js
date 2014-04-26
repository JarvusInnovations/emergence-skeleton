/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext,POC*/
Ext.define('Jarvus.container.Collapse',{
	extend: 'Ext.Container'
	,xtype: 'collapsecontainer'
	,config: {
		cls: 'collapse-container'
		,collapseDirection: 'up'
		,titleBar: null
		,title: null
		,titleBarCls: ''
		,startCollapsed: false
	}
	,initTitleBar: function(){
		var me = this
			,parent = me.parent
			,index = parent.indexOf(me)
			,direction = me.config.collapseDirection
			,title = me.config.title ? me.config.title : 'Panel'
			,titleBarCls = me.config.titleBarCls ? me.config.titleBarCls : ''
			,insertIndex = index + ((direction == 'right' || direction == 'down' ) ? 1 : -1);
			
		
		if(!me.config.title)
			console.warn('You should define a title for the container.');
			
			
		if(!me.titleBar)
		{
			me.titleBar = Ext.create('Ext.TitleBar',{
				title: title
				,attachedContainer: me
				,hidden: true
				,cls: titleBarCls
				,showAnimation: {
					direction: direction
					,reverse: true
					,duration: 250
					,type: 'slide'
				}
				,hideAnimation: {
					direction: direction
					,duration: 250
					,type: 'slide'
				}
			});
			
			me.titleBar.element.on('tap', me.expand, me);
		}
		
		switch(parent.getLayout().config.type)
		{
			case 'hbox':{
				if(direction != 'left' && direction != 'right')
				{
					return console.error('Collapse direction must be left or right if parent container has an hbox layout');
				}
				else
				{
					me.titleBar.setWidth(50);
				}
				break;
			}
			case 'vbox':{
				if(direction != 'up' && direction != 'down')
				{
					return console.error('Collapse direction must be up or down if parent container has an vbox layout');
				}
				else{	
					me.titleBar.setHeight(50);
				}
				break;
			}
		}
		me.addCls('direction-'+direction);
		
		parent.insert((insertIndex == -1 ? 0 : insertIndex), me.titleBar);
		
		if(!me.config.startCollapsed)
		{
			me.addCls('expanded');
		}
		else
		{
			me.addCls('collapsed')
			me.titleBar.show();
		}
	}
	,initialize: function(){
		var me = this
		me.callParent(arguments);
		
		me.on('painted', me.initTitleBar, me , {single: true});
	
		me.element.on('swipe', me.onSwipe, me);	
	}
	,collapse: function(){
		var me = this;
		if(me.element.hasCls('collapsed'))
			return false;
			
		me.replaceCls('expanded', 'collapsed');
		
		setTimeout(function(){
			me.titleBar.show();
		}, 250);
	}
	,expand: function(){
		var me = this;
		if(me.element.hasCls('expanded'))
			return false;
			
		me.titleBar.hide();
		me.replaceCls('collapsed', 'expanded');
		
	}
	,onSwipe: function(evt, node, options){
		var me = this;
		
		if(evt.direction == me.config.collapseDirection)
			me.collapse();
	}
});