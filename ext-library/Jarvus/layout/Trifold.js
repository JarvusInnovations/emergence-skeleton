/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.layout.Trifold', {
	extend: 'Ext.layout.HBox'
	,alias: ['layout.trifold']
	
	,config: {
		sideWidth: 320
		,detailsVisible: false
	}
	
	// stores a list of child components that have handlers for detailsVisibleChange
	,monitoringChildren: []

	,setContainer: function(container) {
		var me = this;
		
		me.callParent(arguments);
		
		container.on('resize', 'onResize', me);
		container.addCls('jarvus-layout-trifold');
	}
	
	,insertInnerItem: function(item, index) {
		var me = this
			,detailsVisible = me.getDetailsVisible()
			,container = me.container
			,nextSibling
			,monitoringChildren = item.isContainer ? item.query('[onDetailsVisibleChange]') : []
			,i = 0;

		if(item.config.region == 'nav') {
			nextSibling = me.detailsRegion || me.contentRegion;
			me.navRegion = item;
			item.collapsed = detailsVisible;
			item.addCls('nav-panel');
			item.setWidth(me.getSideWidth());
		}
		else if(item.config.region == 'details') {
			nextSibling = me.contentRegion;
			me.detailsRegion = item;
			item.collapsed = !detailsVisible;
			item.addCls('details-panel');
			item.setWidth(me.getSideWidth());
		}
		else if(item.config.region == 'content') {
			me.contentRegion = item;
			item.addCls('content-panel');
			item.setWidth(704);
			
			if(detailsVisible) {
				item.addCls('reveal-details');
			}
		}

		// fire initial detailsVisible value on all monitoringChildren
		for(;i < monitoringChildren.length; i++) {
			monitoringChildren[i].config.onDetailsVisibleChange(monitoringChildren[i], detailsVisible, container);
		}
		
		// merge into central list
		me.monitoringChildren = me.monitoringChildren.concat(monitoringChildren);
		
		container.innerElement.dom.insertBefore(item.element.dom, nextSibling ? nextSibling.element.dom : null);
		
		return me;
	}
	
	,updateDetailsVisible: function(detailsVisible) {
		var me = this
			,container = me.container
			,contentRegion = me.contentRegion
			,navRegion = me.navRegion
			,detailsRegion = me.detailsRegion
			,monitoringChildren = me.monitoringChildren
			,i = 0;

		if(contentRegion) {
			contentRegion[detailsVisible ? 'addCls' : 'removeCls']('reveal-details');
		}
		
		if(navRegion) {
			navRegion.collapsed = detailsVisible;
			navRegion.fireEvent(detailsVisible ? 'collapse' : 'expand', navRegion, me);
		}
		
		if(detailsRegion) {
			detailsRegion.collapsed = !detailsVisible;
			detailsRegion.fireEvent(detailsVisible ? 'expand' : 'collapse', detailsRegion, me);
		}
		
		for(; i < monitoringChildren.length; i++) {
			monitoringChildren[i].config.onDetailsVisibleChange(monitoringChildren[i], detailsVisible, container);
		}

		container.fireEvent('detailsvisiblechange', container, detailsVisible);
	}
	
	,onResize: function() {
		var me = this;
		
		if(me.contentRegion) {
			me.contentRegion.setWidth(me.container.innerElement.getWidth() - me.getSideWidth());
		}
	}
});