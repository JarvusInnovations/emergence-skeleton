/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A base class for table cells that contain a clickable icon
 */
Ext.define('Jarvus.table.IconCell', {
	extend: 'Ext.Component'
	,xtype: 'tableiconcell'
	
	/**
	 * @cfg {String}
	 * A CSS class that the icon will be bound to
	 */
	,iconCls: null
	
	/**
	 * @cfg {String}
	 * An optional href attribute for the a tag
	 */
	,href: null
	
	,autoEl: 'td'
	,renderTpl: '<a class="table-icon<tpl if="iconCls"> {iconCls}</tpl>"<tpl if="href"> href="{href}"</tpl>></a>'
	
	,initComponent: function() {
		this.addEvents(
			/**
			 * @event
			 * Fired when the icon is clicked
			 * @param {Jarvus.table.IconCell} this
			 * @param {Ext.EventOpts} ev The underyling {@link Ext.EventObject}
			 * @param {HTMLElement} t The underyling target of the event
			 */
			'click'
		);
		this.callParent();
	}
	
	,beforeRender: function() {
		var me = this;
		
		Ext.apply(me.renderData, {
			iconCls: me.iconCls
			,href: me.href
		});
		
		me.callParent();
	}
	
	,afterRender: function() {
		var me = this;
		
		me.mon(me.el, 'click', function(ev, t) {
			ev.stopEvent();
			
			me.fireEvent('click', me, ev, t);
		});
		
		me.callParent();
	}
});