/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/
Ext.define('Jarvus.layout.Accordion', {
	extend: 'Ext.layout.Default'
	,alias: ['layout.accordion']
	
	,config: {
		expandedItem: null
		,allowCollapse: true
	}
	
	,constructor: function() {
		var me = this;
		me.callParent(arguments);
		me.syncScrollerHeightBuffered = Ext.Function.createBuffered(me.syncScrollerHeight, 100, me);
	}

	,setContainer: function(container) {
		var me = this
			,options = { delegate: '> component' };
		
		me.callParent(arguments);

		container.innerElement.on('tap', 'onHeaderTap', me, { delegate: '.accordion-header' });
		
		container.on('hide', 'onItemHide', me, options)
		         .on('show', 'onItemShow', me, options)
		         .on('expand', 'onItemExpand', me, options)
		         .on('collapse', 'onItemCollapse', me, options);
		         
		container.addCls('jarvus-layout-accordion');
	}
	
	,insertInnerItem: function(item, index) {
		var me = this
			,container = me.container
			,itemDom = item.element.dom
			,nextSibling = container.getInnerAt(index + 1)
			,nextSiblingDom = nextSibling ? nextSibling.accordion.dom : null
			,accordion;

		item.element.addCls('accordion-body');
		accordion = container.innerElement.createChild({
			tag: 'section'
			,cls: 'accordion-section'
			,cn: {
				tag: 'h1'
				,cls: 'accordion-header'
				,html: item.config.title
			}
		}, nextSiblingDom);
		
		if(item.isHidden()) {
			accordion.hide();
		}
			
		accordion.dom.appendChild(itemDom);
		
		accordion.item = item;
		item.accordion = accordion;
		
		return me;
	}
	
	,removeInnerItem: function(item) {
		item.accordion.detach();
	}
	
	,getTotalHeight: function() {
		var innerItems = this.container.getInnerItems()
			,len = innerItems.length
			,i = 0
			,totalHeight = 0
			,containerItem;
		
		for(; i < len; i++) {
			containerItem = innerItems[i];
			if(!containerItem.getHidden()) {
				totalHeight += containerItem.accordion.getHeight();
			}
		}
		
		return totalHeight;
	}
	
	,onHeaderTap: function(ev, t) {
		var item = ev.getTarget('.accordion-section', this.container.innerElement, true).item;
		
		if(item.collapsed) {
			item.expand();
		}
		else if(this.getAllowCollapse()) {
			item.collapse();
		}
	}
	
	,applyExpandedItem: function(item) {
		if(!item && item !== 0) {
			return null;
		}
		
		if(item.isElement) {
			return item.item;
		}
		
		if(Ext.isNumber(item)) {
			return this.container.getInnerAt(item);
		}
		
		return item;
	}
	
	,updateExpandedItem: function(item, oldItem) {
		if(oldItem) {
			oldItem.collapse();
		}
		
		if(item) {
			item.expand();
		}
	}
	
	,onItemAdd: function(item) {
		var me = this;
		
		me.callParent(arguments);
		
		item.collapsed = true;
		
		item.expand = function() {
			if(item.collapsed && item.fireEvent('beforeexpand', item, me) !== false) {
				item.collapsed = false;
				item.fireEvent('expand', item, me);
			}
		};
		
		item.collapse = function() {
			if(!item.collapsed && item.fireEvent('beforecollapse', item, me) !== false) {
				item.collapsed = true;
				item.fireEvent('collapse', item, me);
			}
		};
	}
	
	,onItemHide: function(item) {
		item.accordion.hide();
	}
	
	,onItemShow: function(item) {
		item.accordion.show();
	}
	
	,onItemExpand: function(item) {
		var me = this
			,container = me.container
			,scrollable = container.getScrollable()
			,scroller = scrollable ? scrollable.getScroller() : null;
			
		me.setExpandedItem(item);

		if(item && me.shouldItemBeMaximized(item)) {
			item.setHeight(container.element.getHeight() - me.getTotalHeight());
			if(scroller) {
				scroller.setDisabled(true);
			}
		}
		else if(scroller) {
			me.syncScrollerHeightBuffered();
			scroller.setDisabled(false);
		}
		
		item.accordion.addCls('selected');
	}
	
	,onItemCollapse: function(item) {
		var me = this;
			
		if(me.getExpandedItem() === item) {
			me.setExpandedItem(null);
		}
		
		item.accordion.removeCls('selected');
		
		me.syncScrollerHeightBuffered();
	}
	
	,remaximizeExpanded: function() {
		var expandedItem = this.getExpandedItem();
		
		if(expandedItem && this.shouldItemBeMaximized(expandedItem)) {
			expandedItem.collapse();
			expandedItem.expand();
		}
	}
	
	/**
	 * Force scroller to update height just like Ext.dataview.List does
	 * 
	 * Maybe SizeMonitor will actually work in future ST version and this will be unecessary?
	 */
	,syncScrollerHeight: function() {
		var me = this
			,scrollable = me.container.getScrollable()
			,scroller = scrollable ? scrollable.getScroller() : null;
		
		if(scroller) {
			scroller.givenSize = me.getTotalHeight();
			scroller.refresh();
		}
	}
	
	/**
	 * Test if given item should be maximized
	 */
	,shouldItemBeMaximized: function(item) {
		return !this.container.getScrollable() || !!item.config.maximizeHeight;
	}
});