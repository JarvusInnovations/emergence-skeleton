/*jslint browser: true, undef: true, white: false, laxbreak: true *//*global Ext*/

/**
 * A container for charts that handles zooming and synchronizing optional title and legend components
 */
Ext.define('Jarvus.widget.chart.Container', {
	extend: 'Ext.Container'
	,xtype: 'chartcontainer'
	,requires: [
		'Ext.window.Window'
	]
	
	/**
	 * @cfg {String}
	 * ComponentQuery that will find the contained {@link Ext.chart.Cart chart}
	 */
	,chartSelector: 'chart'

	/**
	 * @cfg {String}
	 * ComponentQuery that will find the contained {@link Jarvus.widget.chart.Title chart title}
	 */
	,titleSelector: 'charttitle'
	
	/**
	 * @cfg {String}
	 * ComponentQuery that will find the contained {@link Jarvus.widget.chart.Legend chart legend}
	 */
	,legendSelector: 'chartlegend'
	
	/**
	 * @cfg {Boolean}
	 * True to enable zooming into the chart in a modal window
	 */
	,zoomable: false
	
	,componentCls: 'chart-container'
	,layout: 'anchor'
	,defaults: {
		anchor: '100%'
	}
	
	,initComponent: function() {
		var me = this;
		
		if(me.zoomable)
		{
			if(!Ext.isObject(me.zoomable))
			{
				me.zoomable = {};
			}
			
			me.addCls('zoomable');
		}
		
		me.callParent(arguments);
	}
	
	,initEvents: function() {
		var me = this
			,chartCmp = me.getChart()
			,titleCmp = me.getTitle()
			,legendCmp = me.getLegend();
			
		if(chartCmp && legendCmp)
		{
			chartCmp.on('refresh', legendCmp.onChartRefresh, legendCmp);
		}
			
		if(chartCmp && titleCmp)
		{
			chartCmp.on('refresh', titleCmp.onChartRefresh, titleCmp);
		}
		
		if(me.zoomable)
		{
			me.mon(me.el, 'click', me.onZoomClick, me);
		}
		
		me.callParent();
	}
	
	,getChart: function() {
		return this.down(this.chartSelector);
	}
	
	,getTitle: function() {
		return this.down(this.titleSelector);
	}
	
	,getLegend: function() {
		return this.down(this.legendSelector);
	}
	
	,onZoomClick: function() {
		var me = this
			,originChart = me.getChart()
			,zoomable = me.zoomable;
			
		if(!me.window || me.window.isDestroyed)
		{
			me.window = Ext.create('Ext.window.Window', {
				layout: 'fit'
				,width: zoomable.width || 600
				,height: zoomable.height || 500
				,title: zoomable.title || 'Chart'
				,modal: true
//				,constrain: true
				
				,items: Ext.applyIf(zoomable, {
					store: originChart.getStore()
				})
			});
			
			if(Ext.isFunction(zoomable.getWindowContainer))
			{
				zoomable.getWindowContainer(me, originChart, window, zoomable).add(me.window);
			}
		}
		
		me.window.show(originChart);
	}
	
	// this implementation moves the original chart to a window
//	,onZoomClick: function() {
//		var me = this
//			,chart = me.getChart()
//			,chartIndex = me.items.indexOf(chart)
//			,chartWin, placeholderCmp;
//
//		Ext.suspendLayouts();
//		
//		// create placeholder and remove chart
//		placeholderCmp = me.insert(chartIndex, {
//			xtype: 'component'
//			,width: chart.getWidth()
//			,height: chart.getHeight()
//		});
//		
//		me.remove(chart, false);
//		
//		// create window containing chart
//		chartWin = Ext.create('Ext.window.Window', {
//			layout: 'fit'
//			,items: chart
//			,width: me.zoomable.width || 600
//			,height: me.zoomable.height || 500
//			,modal: true
//			,title: me.zoomable.title || 'Chart'
//		});
//		
//		// attach close handler
//		chartWin.on('beforeclose', function() {
//			Ext.suspendLayouts();
//			me.remove(placeholderCmp);
//			me.insert(chartIndex, chart);
//			Ext.resumeLayouts(true);
//		}, null, {single: true});
//		
//		// show chart
//		Ext.resumeLayouts(true);
//		chartWin.show(placeholderCmp);
//	}
});